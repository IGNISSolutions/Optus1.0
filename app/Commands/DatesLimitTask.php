<?php

namespace App\Commands;

use Psr\Container\ContainerInterface;
use App\Http\Controllers\BaseController;
use App\Models\Concurso;
use App\Models\ProposalStatus;
use App\Models\InvitationStatus;
use App\Services\EmailService;
use Carbon\Carbon;
use App\Models\OffererCompany;
use DateTimeZone;
use DateTime;
use App\Models\Mailer;



class DatesLimitTask extends BaseController
{
    public $emailService = null;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->emailService = new EmailService();
    }

    /**
     * DatesLimitTask command
     *
     * @param array $args
     * @return void
     */
    public function command($args)
    {
        $this->printLog('EJECUTANDO CRON DE FECHAS LÍMITE');

        $this->processOferentes();

        $this->processConcursos();
    }

    private function processOferentes()
    {
        $concursos = Concurso::where('adjudicado', false)->get();

        $this->printLog($concursos->count() . ' registros a procesar.');

        foreach ($concursos as $concurso) {
            date_default_timezone_set($concurso->cliente->customer_company->timeZone);
            
            $customerCompanyId = isset($concurso->cliente) && !empty($concurso->cliente->customer_company_id)
                ? (int)$concurso->cliente->customer_company_id
                : null;

            if (!isset($_SESSION) || !is_array($_SESSION)) { $_SESSION = []; }
            if ($customerCompanyId) {
                $_SESSION['customer_company_id'] = $customerCompanyId;
            } else {
                unset($_SESSION['customer_company_id']); // fallback a ENV/OPTUS
            }

            // Reinstanciar el servicio para que el constructor tome SMTP/From/Logo correctos
            $this->emailService = new EmailService();

            // Alias "From" opcional desde mailer (si no hay, quedará vacío y el servicio usa su default)
            $aliasFrom = '';
            if ($customerCompanyId) {
                $mailerRow = Mailer::where('customer_company_id', $customerCompanyId)->first();
                if ($mailerRow && !empty($mailerRow->alias)) {
                    $aliasFrom = $mailerRow->alias;
                }
            }

            $concurso_id       = $concurso->id;
            $concurso_nombre   = $concurso->nombre;
            $fecha_economica   = $concurso->fecha_limite_economicas;
            $fecha_tecnica     = $concurso->ficha_tecnica_fecha_limite;
            $fecha_segunda_ronda = $concurso->segunda_ronda_fecha_limite;

            $this->printLog('****ENVÍOS PARA CONCURSO ' . strtoupper($concurso_nombre) . '****');
            $sent_count_1 = 0; // invitación/convocatoria
            $sent_count_2 = 0; // técnicas
            $sent_count_3 = 0; // económicas
            $sent_count_4 = 0; // segunda ronda (si algún día se habilita)
            $sent_count_5 = 0; // muro

            // Iteramos TODOS los oferentes
            $oferentes = $concurso->oferentes;

            foreach ($oferentes as $oferente) {
                // === TU BLOQUE: company + users (intacto) ===
                $company = $oferente->company()->with('users')->first();
                if (!$company) {
                    $this->printLog("Oferente {$oferente->id} sin compañía asociada, se omite.");
                    continue;
                }

                $users = $company->users
                    ->pluck('email')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($users)) {
                    $this->printLog("Compañía {$company->id} ({$company->business_name}) sin usuarios con email, se omite envío.");
                    continue;
                }
                // === FIN BLOQUE ===

                // Flags
                $nombre               = $oferente->company->business_name;
                $invitacion_pendiente = $oferente->is_invitacion_pendiente;
                $invitacion_rechazada = $oferente->is_invitacion_rechazada;
                $invitacion_aceptada  = $oferente->has_invitacion_aceptada;
                $presento_economica   = $oferente->has_economica_presentada;
                $presento_tecnica     = $oferente->has_tecnica_presentada;

                // “Seleccionado”: úsalo SOLO para etapas posteriores
                $selected = (bool) ($oferente->is_seleccionado ?? optional($oferente->pivot)->is_seleccionado ?? false);

                if ($invitacion_rechazada) {
                    continue;
                }

                /**
                 * INVITACIÓN / CONVOCATORIA
                 * - No exige “selected”.
                 * - Dispara cuando faltan exactamente 3, 2 o 1 días (corre 1 vez al día).
                 *   Si tu límite de aceptación no es $concurso->fecha_limite, reemplazalo por el campo correcto.
                 */
                $daysLeftInv   = Carbon::now()->diffInDays($concurso->fecha_limite, false);
                $fecha_vencida = $oferente->has_invitacion_vencida;

                $this->printLog(sprintf(
                    "[INV] oferente:%d pend:%s venc:%s daysLeft:%d",
                    $oferente->id,
                    $invitacion_pendiente ? '1' : '0',
                    $fecha_vencida ? '1' : '0',
                    (int)$daysLeftInv
                ));

                if ($invitacion_pendiente && !$fecha_vencida && in_array((int)$daysLeftInv, [3, 2, 1], true)) {
                    $title    = 'Invitación a Concurso de Precios';
                    $subject  = $concurso->nombre . ' - ' . $title;
                    $template = rootPath(config('app.templates_path')) . '/email/invitation.tpl';

                    $html = $this->fetch($template, [
                        'title'         => $title,
                        'ano'           => Carbon::now()->format('Y'),
                        'concurso'      => $concurso,
                        'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('Y-m-d H:i') : 'No aplica',
                        'company_name'  => $oferente->company->business_name,
                        'timeZone'      => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
                    ]);

                    $result = $this->emailService->send($html, $subject, $users, "");

                    if ($result['success']) {
                        $this->printLog('Invitación enviada a ' . $nombre . ' (Invitación/Convocatoria)');
                        $sent_count_1++;
                    } else {
                        $this->printLog('ERROR: Invitación no enviada a ' . $nombre . ' (Invitación/Convocatoria)');
                        $this->printLog($result['message']);
                    }
                } elseif ($fecha_vencida && $invitacion_pendiente) {
                    if ($this->rechazarOferente($oferente, $concurso, 'invitation')) {
                        $this->printLog('Oferente ' . $nombre . ' rechazado (Invitación/Convocatoria) por superar fecha límite.');
                        continue;
                    }
                }

                /**
                 * PRESENTACIÓN TÉCNICAS
                 * - Opcional: exigir $selected para no notificar a no seleccionados.
                 */
                $daysLeftTech  = Carbon::now()->diffInDays($fecha_tecnica, false);
                $fecha_vencida = $oferente->has_tecnica_vencida;

                if ($concurso->technical_includes && $invitacion_aceptada && !$fecha_vencida && !$presento_tecnica && $daysLeftTech >= 0 && $daysLeftTech <= 3) {
                    $title    = 'Fecha límite para presentar ofertas técnicas (Recordatorio)';
                    $subject  = $concurso->nombre . ' - ' . $title;
                    $template = rootPath(config('app.templates_path')) . '/email/dates_limit_technical.tpl';

                    $html = $this->fetch($template, [
                        'title'        => $title,
                        'ano'          => Carbon::now()->format('Y'),
                        'concurso'     => $concurso,
                        'company_name' => $oferente->company->business_name,
                        'timeZone'     => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
                    ]);

                    $result = $this->emailService->send($html, $subject, $users, "");

                    if ($result['success']) {
                        $this->printLog('Invitación enviada a ' . $nombre . ' (Presentaciones Técnicas)');
                        $sent_count_2++;
                    } else {
                        $this->printLog('ERROR: No se pudo enviar a ' . $nombre . ' (Presentaciones Técnicas)');
                        $this->printLog($result['message']);
                    }
                } elseif ($fecha_vencida && !$presento_tecnica) {
                    if ($this->rechazarOferente($oferente, $concurso, 'technical')) {
                        $this->printLog('Oferente ' . $nombre . ' rechazado (Presentaciones Técnicas) por superar fecha límite.');
                        continue;
                    }
                }

                /**
                 * PRESENTACIÓN ECONÓMICAS
                 */
                $daysLeftEco   = Carbon::now()->diffInDays($fecha_economica, false);
                $fecha_vencida = $oferente->has_economica_vencida;

                if ($concurso->is_sobrecerrado) {
                    if ($invitacion_aceptada && !$fecha_vencida && !$presento_economica && $daysLeftEco >= 0 && $daysLeftEco <= 3) {
                        $title    = 'Fecha límite para presentar ofertas económicas (Recordatorio)';
                        $subject  = $concurso->nombre . ' - ' . $title;
                        $template = rootPath(config('app.templates_path')) . '/email/dates_limit_economic.tpl';

                        $html = $this->fetch($template, [
                            'title'        => $title,
                            'ano'          => Carbon::now()->format('Y'),
                            'concurso'     => $concurso,
                            'company_name' => $oferente->company->business_name,
                            'timeZone'     => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
                        ]);

                        $result = $this->emailService->send($html, $subject, $users, "");

                        if ($result['success']) {
                            $this->printLog('Invitación enviada a ' . $nombre . ' (Presentaciones Económicas)');
                            $sent_count_3++;
                        } else {
                            $this->printLog('ERROR: No se pudo enviar a ' . $nombre . ' (Presentaciones Económicas)');
                            $this->printLog($result['message']);
                        }
                    } elseif ($fecha_vencida && !$presento_economica) {
                        if ($this->rechazarOferente($oferente, $concurso, 'economic')) {
                            $this->printLog('Oferente ' . $nombre . ' rechazado (Presentaciones Económicas) por superar fecha límite.');
                            continue;
                        }
                    }
                }

                /**
                 * SUBASTA (si aplica)
                 */
                if ($concurso->is_online) {
                    $hoursLeftAuction = Carbon::now()->diffInHours($concurso->inicio_subasta, false);

                    if ($hoursLeftAuction <= 48 && $hoursLeftAuction >= 0) {
                        $title    = 'Fecha inicio de SUBASTA';
                        $subject  = $concurso->nombre . ' - ' . $title;
                        $template = rootPath(config('app.templates_path')) . '/email/reminder-subasta.tpl';

                        $html = $this->fetch($template, [
                            'title'        => $title,
                            'ano'          => Carbon::now()->format('Y'),
                            'concurso'     => $concurso,
                            'company_name' => $oferente->company->business_name,
                        ]);

                        $result = $this->emailService->send($html, $subject, $users, "");

                        if ($result['success']) {
                            $this->printLog('Invitación enviada a ' . $nombre . ' (Recordatorio Subasta)');
                            // si querés contar aparte, podrías sumar a otro contador
                        } else {
                            $this->printLog('ERROR: No se pudo enviar a ' . $nombre . ' (Recordatorio Subasta)');
                            $this->printLog($result['message']);
                        }
                    }
                }

                /**
                 * MURO MENSAJES
                 */
                $daysLeftChat = Carbon::now()->diffInDays($concurso->finalizacion_consultas, false);
                $diasCierre   = 5;

                if ($daysLeftChat <= $diasCierre && $daysLeftChat >= 0 && $concurso->is_chat_enabled && $invitacion_aceptada) {
                    $title    = 'Fecha límite para consultas en el muro de mensaje';
                    $subject  = $concurso->nombre . ' - ' . $title;
                    $template = rootPath(config('app.templates_path')) . '/email/dates_limit_chat.tpl';

                    $html = $this->fetch($template, [
                        'title'        => $title,
                        'ano'          => Carbon::now()->format('Y'),
                        'concurso'     => $concurso,
                        'company_name' => $oferente->company->business_name,
                    ]);

                    $result = $this->emailService->send($html, $subject, $users, "");

                    if ($result['success']) {
                        $this->printLog('Invitación enviada a ' . $nombre . ' (Envío de Fecha Límite Muro)');
                        $sent_count_5++;
                    } else {
                        $this->printLog('ERROR: No se pudo enviar a ' . $nombre . ' (Envío de Fecha Límite Muro)');
                        $this->printLog($result['message']);
                    }
                }
            } // foreach oferentes

            $this->printLog($sent_count_1 . ' correos enviados para Invitación/Convocatoria.');
            $this->printLog($sent_count_2 . ' correos enviados para Presentaciones Técnicas.');
            $this->printLog($sent_count_3 . ' correos enviados para Presentaciones Económicas.');
            $this->printLog($sent_count_4 . ' correos enviados para Segunda Ronda de Ofertas.');
            $this->printLog($sent_count_5 . ' correos enviados para Envío de Fecha Límite.');
        } // foreach concursos
    }



    private function processConcursos()
    {
        // $concursos = collect();
        // $concursos = $concursos->merge(Concurso::where([
        //     ['adjudicado', '=', false]
        // ])
        //     ->get()
        //     ->filter(function ($concurso) {
        //         return
        //             $concurso->fecha_limite < Carbon::now() &&
        //             $concurso->oferentes
        //                 ->where('has_invitacion_aceptada', true)
        //                 ->count() == 0;
        //     }))->unique('id');

        // $concursos = $concursos->merge(Concurso::where([
        //     ['adjudicado', '=', false]
        // ])
        //     ->get()
        //     ->filter(function ($concurso) {
        //         return
        //             ($concurso->technical_includes && $concurso->ficha_tecnica_fecha_limite < Carbon::now()) &&
        //             $concurso->oferentes
        //                 ->where('has_tecnica_presentada', true)
        //                 ->count() == 0;
        //     }))->unique('id');

        // $concursos = $concursos->merge(Concurso::where([
        //     ['adjudicado', '=', false]
        // ])
        //     ->get()
        //     ->filter(function ($concurso) {
        //         return
        //             (isset($concurso->fecha_limite_economicas) ? $concurso->fecha_limite_economicas : Carbon::now()->addMinutes(5)) < Carbon::now() &&
        //             $concurso->oferentes
        //                 ->where('has_economica_presentada', true)
        //                 ->count() == 0;
        //     }))->unique('id');

        // if ($concursos->count() == 0) {
        //     return;
        // }

        // $this->printLog('CANCELACIÓN DE CONCURSOS');
        // $this->printLog('Se han encontrado ' . $concursos->count() . ' concursos obsoletos, se procede a eliminarlos.');

        // $sent_count_6 = 0;
        // foreach ($concursos as $concurso) {
        //     $user = $concurso->cliente;

        //     try {
        //         $capsule = dependency('db');
        //         $connection = $capsule->getConnection();
        //         $connection->beginTransaction();

        //         $concurso->delete();

        //         $connection->commit();

        //     } catch (\Exception $e) {
        //         $connection->rollBack();
        //         $this->printLog(
        //             'ERROR: No pudo eliminarse el concurso ' .
        //             $concurso->nombre .
        //             ' para Cliente ' .
        //             $user->full_name .
        //             '.'
        //         );
        //         $this->printLog($e->getMessage());
        //         continue;
        //     }

        //     $title = 'Concurso Cancelado';
        //     $subject = $concurso->nombre . ' - ' . $title;
        //     $template = rootPath(config('app.templates_path')) . '/email/cancellation_automatic.tpl';

        //     $html = $this->fetch($template, [
        //         'title' => $title,
        //         'ano' => Carbon::now()->format('Y'),
        //         'concurso' => $concurso,

        //     ]);

        //     $result = $this->emailService->send($html, $subject, $user->email, "");

        //     if ($result['success']) {
        //         $this->printLog('Aviso de cancelación de Concurso ' . $concurso->nombre . ' para Cliente ' . $user->full_name);
        //         $sent_count_6++;
        //     } else {
        //         $this->printLog('ERROR: No ha podido enviarse aviso de cancelación de Concurso ' . $concurso->nombre . ' para Cliente ' . $user->full_name);
        //         $this->printLog($result['message']);
        //     }
        // }

        // $this->printLog($sent_count_6 . ' correos enviados para Cancelación de Concursos.');
    }

    // Pring in SYSLOG
    private function printLog($message = null)
    {
        if ($message) {
            logger('cron')->info($message . "\n");
            echo $message . "\n";
        }
    }

    private function rechazarOferente($oferente, $concurso, $etapa)
    {
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            switch ($etapa) {
                case 'invitation':
                    $invitation = $oferente->invitation;
                    $invitation_status = InvitationStatus::where('code', InvitationStatus::CODES['expired'])->first();

                    $invitation->update([
                        'status_id' => $invitation_status->id
                    ]);
                    break;
                case 'technical':
                    $proposal = $oferente->technical_proposal;
                    if ($proposal) {
                        $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['expired'])->first();
                        $proposal->update([
                            'status_id' => $proposal_status->id
                        ]);
                    }
                    break;
                case 'economic':
                    $proposal = $oferente->economic_proposal;
                    if ($proposal) {
                        $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['expired'])->first();
                        $proposal->update([
                            'status_id' => $proposal_status->id
                        ]);
                    }
                    break;
            }

            $oferente->update([
                'rechazado' => false
            ]);

            $connection->commit();

        } catch (\Exception $e) {
            $connection->rollback();
            $this->printLog(
                'ERROR: No pudo rechazarse el oferente ' .
                $oferente->user->full_name .
                '(' . $oferente->user->offerer_company->business_name . ')' .
                ' para el Concurso ' .
                $concurso->nombre .
                '.'
            );
            $this->printLog($e->getMessage());
            return false;
        }

        return true;
    }

    private function toGmtOffset($timezone)
    {
        $userTimeZone = new DateTimeZone($timezone);
        $offset = $userTimeZone->getOffset(new DateTime("now", new DateTimeZone('GMT'))); // Offset in seconds
        $seconds = abs($offset);
        $sign = $offset > 0 ? '+' : '-';
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        return sprintf($timezone . ' ' . "(GMT$sign%02d:%02d)", $hours, $mins, $secs);
    }

    private function sendMailExpired($oferente, $concurso, $etapa, $users)
    {
        $title = 'Concurso Expirado';
        $subject = $concurso->nombre . ' - ' . $title;
        $template = rootPath(config('app.templates_path')) . '/email/expired_email.tpl';

        $html = $this->fetch($template, [
            'ano' => Carbon::now()->format('Y'),
            'title' => $title,
            'etapa' => $etapa,
            'concurso' => $concurso,
            'company_name' => $oferente->company->business_name,
        ]);

        $result = $this->emailService->send($html, $subject, $users, "");
        return $result;
    }
}