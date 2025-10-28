<?php

namespace App\Commands;

use App\Models\Concurso;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;

class EconomicStageEndTask
{
    /**
     * Verificar concursos cuya etapa económica haya finalizado y enviar emails
     */
    public function execute()
    {
        try {
            $now = Carbon::now();
            
            echo "\n===========================================\n";
            echo "Buscando concursos finalizados...\n";
            echo "Fecha actual: " . $now->format('Y-m-d H:i:s') . "\n";
            echo "===========================================\n\n";
            
            // Buscar concursos cuya fecha_limite_economicas haya expirado
            // y que aún no se les haya enviado el email de finalización
            $concursos = Concurso::whereNotNull('fecha_limite_economicas')
                ->where('fecha_limite_economicas', '<', $now)
                ->whereNull('email_economica_enviado_at')
                ->get();

            echo "📊 Estadísticas:\n";
            echo "   - Total concursos con fecha económica: " . Concurso::whereNotNull('fecha_limite_economicas')->count() . "\n";
            echo "   - Concursos finalizados: " . Concurso::whereNotNull('fecha_limite_economicas')->where('fecha_limite_economicas', '<', $now)->count() . "\n";
            echo "   - Sin email enviado: " . $concursos->count() . "\n\n";

            if ($concursos->isEmpty()) {
                echo "ℹ️  No se encontraron concursos que requieran envío de email\n\n";
                
                // Mostrar algunos concursos para debugging
                echo "--- DEBUGGING: Últimos 5 concursos con fecha económica ---\n";
                $debug = Concurso::whereNotNull('fecha_limite_economicas')
                    ->orderBy('fecha_limite_economicas', 'desc')
                    ->take(5)
                    ->get(['id', 'nombre', 'fecha_limite_economicas', 'email_economica_enviado_at']);
                    
                foreach ($debug as $d) {
                    echo "ID: {$d->id} | {$d->nombre}\n";
                    echo "  📅 Fecha límite: {$d->fecha_limite_economicas}\n";
                    echo "  ✉️  Email enviado: " . ($d->email_economica_enviado_at ?? 'NO') . "\n";
                    $expirado = Carbon::parse($d->fecha_limite_economicas)->lt($now);
                    echo "  ⏰ ¿Expirado?: " . ($expirado ? 'SÍ' : 'NO') . "\n\n";
                }
            }

            $enviados = 0;
            $errores = 0;

            foreach ($concursos as $concurso) {
                try {
                    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    echo "📝 Procesando Concurso #{$concurso->id}\n";
                    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    echo "Nombre: {$concurso->nombre}\n";
                    echo "Fecha límite: {$concurso->fecha_limite_economicas}\n";
                    echo "ID Cliente: {$concurso->id_cliente}\n";
                    
                    // Obtener el cliente (creador del concurso)
                    $cliente = User::find($concurso->id_cliente);
                    
                    if (!$cliente || !$cliente->email) {
                        echo "❌ ERROR: Cliente no encontrado o sin email\n";
                        $errores++;
                        continue;
                    }

                    echo "👤 Cliente: {$cliente->email}\n";

                    // Preparar datos para el email
                    $email_to = [$cliente->email];
                    $nombre_completo = $cliente->full_name ?? $cliente->email;
                    $subject = 'Finalización de Etapa Económica - ' . $concurso->nombre;
                    
                    // URL del detalle del concurso
                    $base_url = env('APP_SITE_URL', '');
                    $url_concurso = $base_url . '/concursos/cliente/detail/detail/' . $concurso->id;
                    
                    // Renderizar la plantilla de email
                    $templates_path = __DIR__ . '/../../resources/views/templates';
                    $template = $templates_path . '/email/economic-stage-end.tpl';
                    
                    echo "📄 Renderizando plantilla: {$template}\n";
                    
                    // Crear instancia de Smarty manualmente para contexto CLI
                    // Crear directorios si no existen
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
                    
                    $smarty->assign('nombre_cliente', $nombre_completo);
                    $smarty->assign('nombre_concurso', $concurso->nombre);
                    $smarty->assign('fecha_finalizacion', Carbon::now()->format('d/m/Y H:i:s'));
                    $smarty->assign('url_concurso', $url_concurso);
                    $smarty->assign('title', 'Finalización de Etapa Económica');
                    $smarty->assign('ano', Carbon::now()->format('Y'));
                    
                    $htmlMessage = $smarty->fetch($template);

                    echo "📧 Enviando email...\n";
                    
                    // Enviar el email
                    $emailService = new EmailService();
                    $result = $emailService->send($htmlMessage, $subject, $email_to, $nombre_completo);

                    if ($result['success']) {
                        // Marcar que el email fue enviado
                        $concurso->email_economica_enviado_at = Carbon::now();
                        $concurso->save();
                        
                        echo "✅ Email enviado exitosamente\n";
                        $enviados++;
                    } else {
                        echo "❌ Error al enviar: {$result['message']}\n";
                        $errores++;
                    }

                } catch (\Exception $e) {
                    echo "❌ Excepción: {$e->getMessage()}\n";
                    echo "Stack trace:\n{$e->getTraceAsString()}\n";
                    $errores++;
                }
            }

            echo "\n===========================================\n";
            echo "📊 RESUMEN FINAL\n";
            echo "===========================================\n";
            echo "Concursos procesados: " . count($concursos) . "\n";
            echo "✅ Emails enviados: {$enviados}\n";
            echo "❌ Errores: {$errores}\n";
            echo "===========================================\n\n";

            return [
                'success' => true,
                'procesados' => count($concursos),
                'enviados' => $enviados,
                'errores' => $errores
            ];

        } catch (\Exception $e) {
            echo "\n❌ [FATAL ERROR] {$e->getMessage()}\n";
            echo "Stack trace:\n{$e->getTraceAsString()}\n\n";
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
