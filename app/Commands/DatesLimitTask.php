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
            $concurso_id = $concurso->id;
            $concurso_nombre = $concurso->nombre;
            $fecha_economica = $concurso->fecha_limite_economicas;
            $fecha_tecnica = $concurso->ficha_tecnica_fecha_limite;
            $fecha_segunda_ronda = $concurso->segunda_ronda_fecha_limite;

            $this->printLog('****ENVÍOS PARA CONCURSO ' . strtoupper($concurso_nombre) . '****');
            $sent_count_1 = 0;
            $sent_count_2 = 0;
            $sent_count_3 = 0;
            $sent_count_4 = 0;
            $sent_count_5 = 0;

            // Iteramos TODOS los oferentes y dentro decidimos qué correos aplicar
            $oferentes = $concurso->oferentes;

            foreach ($oferentes as $oferente) {
                // === BLOQUE TUYO: company + users (sin cambios) ===
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
                // === FIN BLOQUE TUYO ===

                // Flags comunes
                $nombre               = $oferente->company->business_name;
                $invitacion_pendiente = $oferente->is_invitacion_pendiente;
                $invitacion_rechazada = $oferente->is_invitacion_rechazada;
                $invitacion_aceptada  = $oferente->has_invitacion_aceptada;
                $presento_economica   = $oferente->has_economica_presentada;
                $presento_tecnica     = $oferente->has_tecnica_presentada;

                // is_seleccionado puede venir en la columna del modelo o en el pivote
                $selected = (bool) ($oferente->is_seleccionado ?? optional($oferente->pivot)->is_seleccionado ?? false);

                if (!$invitacion_rechazada) {

                    /**
                     * INVITACIÓN / CONVOCATORIA
                     * (Solo para oferentes SELECCIONADOS)
                     * Envío cuando faltan 3, 2 o 1 días (cron corre 1 vez/día).
                     */
                    $diff = Carbon::now()->diffInDays($concurso->fecha_limite, false);
                    $fecha_vencida = $oferente->has_invitacion_vencida;

                    if ($selected && !$fecha_vencida && $invitacion_pendiente && $diff <= 3 && $diff >= 0) {
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
                            $this->printLog('Invitación enviada a ' . $nombre . ' (Invitación a Concurso/Convocatoria)');
                            $sent_count_1++;
                        } else {
                            $this->printLog('ERROR: La Invitación no ha podido ser enviada a ' . $nombre . ' (Invitación a Concurso/Convocatoria)');
                            $this->printLog($result['message']);
                        }
                    } elseif ($selected && $fecha_vencida && $invitacion_pendiente) {
                        print("entre aqui");
                        if ($this->rechazarOferente($oferente, $concurso, 'invitation')) {
                            $this->printLog('Oferente ' . $nombre . ' rechazado en concurso ' . strtoupper($concurso_nombre) . ' porque superó fecha límite para esta etapa: (Invitación a Concurso/Convocatoria)');
                            // $this->sendMailExpired($oferente, $concurso, 'invitation', $users);
                            continue;
                        }
                    }

                    /**
                     * PRESENTACIÓN TÉCNICAS
                     * (Mantiene tu lógica actual: <= 3 días, si aplica y no presentó)
                     */
                    $diff = Carbon::now()->diffInDays($fecha_tecnica, false);
                    $fecha_vencida = $oferente->has_tecnica_vencida;

                    if ($diff <= 3 && $diff >= 0 && !$fecha_vencida && !$presento_tecnica && $concurso->technical_includes) {
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
                            $this->printLog('ERROR: La Invitación no ha podido ser enviada a ' . $nombre . ' (Presentaciones Técnicas)');
                            $this->printLog($result['message']);
                        }
                    } elseif ($fecha_vencida && !$presento_tecnica) {
                        if ($this->rechazarOferente($oferente, $concurso, 'technical')) {
                            $this->printLog('Oferente ' . $nombre . ' rechazado en concurso ' . strtoupper($concurso_nombre) . ' porque superó fecha límite para esta etapa: (Presentaciones Técnicas)');
                            // $this->sendMailExpired($oferente, $concurso, 'technical', $users);
                            continue;
                        }
                    }

                    /**
                     * PRESENTACIÓN ECONÓMICAS
                     * (Mantiene tu lógica actual: <= 3 días, si aplica y no presentó)
                     */
                    $diff = Carbon::now()->diffInDays($fecha_economica, false);
                    $fecha_vencida = $oferente->has_economica_vencida;

                    if ($concurso->is_sobrecerrado) {
                        if ($diff <= 3 && $diff >= 0 && !$fecha_vencida && !$presento_economica) {
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
                                $this->printLog('Invitación enviada a ' . $nombre . ' (Presentaciones Económica)');
                                $sent_count_3++;
                            } else {
                                $this->printLog('ERROR: La Invitación no ha podido ser enviada a ' . $nombre . ' (Presentaciones Económica)');
                                $this->printLog($result['message']);
                            }
                        } elseif ($fecha_vencida && !$presento_economica) {
                            if ($this->rechazarOferente($oferente, $concurso, 'economic')) {
                                $this->printLog('Oferente ' . $nombre . ' rechazado en concurso ' . strtoupper($concurso_nombre) . ' porque superó fecha límite para esta etapa: (Presentaciones Económicas)');
                                // $this->sendMailExpired($oferente, $concurso, 'economic', $users);
                                continue;
                            }
                        }
                    }

                    /**
                     * SUBASTA (si aplica)
                     */
                    if ($concurso->is_online) {
                        $diff = Carbon::now()->diffInHours($concurso->inicio_subasta, false);

                        if ($diff <= 48 && $diff >= 0) {
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
                                $this->printLog('Invitación enviada a ' . $nombre . ' (Presentaciones Económica)');
                                $sent_count_3++;
                            } else {
                                $this->printLog('ERROR: La Invitación no ha podido ser enviada a ' . $nombre . ' (Presentaciones Económica)');
                                $this->printLog($result['message']);
                            }
                        } elseif ($diff == -1 && !$presento_economica) {
                            if ($this->rechazarOferente($oferente, $concurso, 'economic')) {
                                $this->printLog('Oferente ' . $nombre . ' rechazado en concurso ' . strtoupper($concurso_nombre) . ' porque superó fecha límite para esta etapa: (Presentaciones Económicas)');
                                // $this->sendMailExpired($oferente, $concurso, 'economic', $users);
                                continue;
                            }
                        }
                    }

                    /**
                     * MURO MENSAJES
                     */
                    $diff = Carbon::now()->diffInDays($concurso->finalizacion_consultas, false);
                    $diasCierre = 5;
                    if ($diff <= $diasCierre && $diff >= 0 && $concurso->is_chat_enabled && $invitacion_aceptada) {
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
                            $this->printLog('Invitación enviada a ' . $nombre . ' (Envío de Fecha Límite)');
                            $sent_count_5++;
                        } else {
                            $this->printLog('ERROR: La Invitación no ha podido ser enviada a ' . $nombre . ' (Envío de Fecha Límite)');
                            $this->printLog($result['message']);
                        }
                    }
                } // end if !$invitacion_rechazada
            } // end foreach oferentes

            $this->printLog($sent_count_1 . ' correos enviados para Invitación/Convocatoria.');
            $this->printLog($sent_count_2 . ' correos enviados para Presentaciones Técnicas.');
            $this->printLog($sent_count_3 . ' correos enviados para Presentaciones Económicas.');
            $this->printLog($sent_count_4 . ' correos enviados para Segunda Ronda de Ofertas.');
            $this->printLog($sent_count_5 . ' correos enviados para Envío de Fecha Límite.');
        } // end foreach concursos
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