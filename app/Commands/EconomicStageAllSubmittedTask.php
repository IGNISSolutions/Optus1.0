<?php

namespace App\Commands;

use App\Models\Concurso;
use App\Models\Participante;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;

class EconomicStageAllSubmittedTask
{
    /**
     * Verificar concursos donde TODOS los participantes presentaron su propuesta económica
     * antes de la fecha límite y enviar email al cliente
     */
    public function execute()
    {
        try {
            $now = Carbon::now();
            
            echo "\n===========================================\n";
            echo "Verificando propuestas económicas completadas...\n";
            echo "Fecha actual: " . $now->format('Y-m-d H:i:s') . "\n";
            echo "===========================================\n\n";
            
            // Buscar concursos con fecha económica vigente (aún no vencida)
            // y que NO hayan enviado email aún
            $concursos = Concurso::whereNotNull('fecha_limite_economicas')
                ->where('fecha_limite_economicas', '>', $now) // Fecha NO vencida
                ->whereNull('email_economica_enviado_at') // Email NO enviado
                ->get();

            echo "📊 Estadísticas:\n";
            echo "   - Concursos con fecha económica vigente: " . $concursos->count() . "\n\n";

            if ($concursos->isEmpty()) {
                echo "ℹ️  No hay concursos con etapa económica vigente\n\n";
                return ['success' => true, 'procesados' => 0, 'enviados' => 0, 'errores' => 0];
            }

            $enviados = 0;
            $errores = 0;
            $revisados = 0;

            foreach ($concursos as $concurso) {
                $revisados++;
                
                // Obtener todos los participantes del concurso
                $participantes = Participante::where('id_concurso', $concurso->id)
                    ->get();

                // Si no hay participantes, saltar
                if ($participantes->isEmpty()) {
                    continue;
                }

                // Filtrar participantes que están/estaban en etapa económica pendiente
                $participantesEconomicos = $participantes->filter(function ($p) {
                    return preg_match('/^economica-pendiente(-\d+)?$/', $p->etapa_actual) ||
                           preg_match('/^economica-presentada(-\d+)?$/', $p->etapa_actual) ||
                           preg_match('/^economica-rechazada(-\d+)?$/', $p->etapa_actual);
                });

                // Si no hay participantes en etapa económica, saltar
                if ($participantesEconomicos->isEmpty()) {
                    continue;
                }

                // Verificar si hay alguno TODAVÍA pendiente
                $hayPendientes = $participantesEconomicos->filter(function ($p) {
                    return preg_match('/^economica-pendiente(-\d+)?$/', $p->etapa_actual);
                })->isNotEmpty();

                // Si aún hay pendientes, este concurso NO está completo
                if ($hayPendientes) {
                    continue;
                }

                // TODOS presentaron! Verificar que al menos uno esté en "presentada"
                $hayPresentadas = $participantesEconomicos->filter(function ($p) {
                    return preg_match('/^economica-presentada(-\d+)?$/', $p->etapa_actual);
                })->isNotEmpty();

                if (!$hayPresentadas) {
                    continue; // No hay presentadas (tal vez todos rechazados)
                }

                // Este concurso cumple las condiciones: todos presentaron antes del cierre
                try {
                    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    echo "📝 Concurso #{$concurso->id} - ¡Todos presentaron!\n";
                    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    echo "Nombre: {$concurso->nombre}\n";
                    echo "Fecha límite: {$concurso->fecha_limite_economicas}\n";
                    echo "Participantes económicos: " . $participantesEconomicos->count() . "\n";
                    echo "ID Cliente: {$concurso->id_cliente}\n";
                    
                    // Obtener el cliente (creador del concurso)
                    $cliente = User::find($concurso->id_cliente);
                    
                    if (!$cliente || !$cliente->email) {
                        echo " ERROR: Cliente no encontrado o sin email\n";
                        $errores++;
                        continue;
                    }

                    echo " Cliente: {$cliente->email}\n";

                    // Preparar datos para el email
                    $email_to = [$cliente->email];
                    $nombre_completo = $cliente->full_name ?? $cliente->email;
                    $subject = 'Propuestas Económicas Completadas - ' . $concurso->nombre;
                    
                    // URL del detalle del concurso
                    $base_url = env('APP_SITE_URL', '');
                    $url_concurso = $base_url . '/concursos/cliente/detail/detail/' . $concurso->id;
                    
                    // Renderizar la plantilla de email
                    $templates_path = __DIR__ . '/../../resources/views/templates';
                    $template = $templates_path . '/email/economic-stage-all-submitted.tpl';
                    
                    echo "📄 Renderizando plantilla: {$template}\n";
                    
                    // Crear instancia de Smarty manualmente para contexto CLI
                    $compile_dir = __DIR__ . '/../../storage/tmp/templates_c';
                    $cache_dir = __DIR__ . '/../../storage/tmp/cache';
                    
                    if (!is_dir($compile_dir)) {
                        mkdir($compile_dir, 0777, true);
                    }
                    if (!is_dir($cache_dir)) {
                        mkdir($cache_dir, 0777, true);
                    }
                    
                    $smarty = new \Smarty();
                    $smarty->setTemplateDir($templates_path);
                    $smarty->setCompileDir($compile_dir);
                    $smarty->setCacheDir($cache_dir);
                    
                    // Determinar la ronda económica actual
                    $ronda = $concurso->ronda_actual ?? 1;
                    $rondaTexto = $ronda == 1 ? 'Primera' : ($ronda == 2 ? 'Segunda' : ($ronda == 3 ? 'Tercera' : ($ronda == 4 ? 'Cuarta' : 'Quinta')));
                    
                    $smarty->assign('nombre_cliente', $nombre_completo);
                    $smarty->assign('nombre_concurso', $concurso->nombre);
                    $smarty->assign('ronda_economica', $rondaTexto);
                    $smarty->assign('fecha_completado', Carbon::now()->format('d/m/Y H:i:s'));
                    $smarty->assign('fecha_limite', Carbon::parse($concurso->fecha_limite_economicas)->format('d/m/Y H:i:s'));
                    $smarty->assign('url_concurso', $url_concurso);
                    $smarty->assign('title', 'Propuestas Económicas Completadas');
                    $smarty->assign('ano', Carbon::now()->format('Y'));
                    
                    $htmlMessage = $smarty->fetch($template);

                    echo "Enviando email...\n";
                    
                    // Enviar el email
                    $emailService = new EmailService();
                    $result = $emailService->send($htmlMessage, $subject, $email_to, $nombre_completo);

                    if ($result['success']) {
                        // Marcar que el email fue enviado
                        $concurso->email_economica_enviado_at = Carbon::now();
                        $concurso->save();
                        
                        echo "Email enviado exitosamente\n";
                        $enviados++;
                    } else {
                        echo "Error al enviar: {$result['message']}\n";
                        $errores++;
                    }

                } catch (\Exception $e) {
                    echo "Excepción: {$e->getMessage()}\n";
                    echo "Stack trace:\n{$e->getTraceAsString()}\n";
                    $errores++;
                }
            }

            echo "\n===========================================\n";
            echo "RESUMEN FINAL\n";
            echo "===========================================\n";
            echo "Concursos revisados: {$revisados}\n";
            echo "Emails enviados: {$enviados}\n";
            echo "Errores: {$errores}\n";
            echo "===========================================\n\n";

            return [
                'success' => true,
                'procesados' => $revisados,
                'enviados' => $enviados,
                'errores' => $errores
            ];

        } catch (\Exception $e) {
            echo "\n[FATAL ERROR] {$e->getMessage()}\n";
            echo "Stack trace:\n{$e->getTraceAsString()}\n\n";
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
