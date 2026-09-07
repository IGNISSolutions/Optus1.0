<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Concurso;
use App\Models\Participante;
use App\Models\User;
use App\Models\Invitation;
use App\Models\InvitationStatus;
use App\Services\EmailService;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\Ciudad;
use App\Models\Area;
use App\Models\OffererCompany;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;

class InvitationController extends BaseController
{
    public function send(Request $request, Response $response)
    {
        date_default_timezone_set(user()->customer_company->timeZone);
        $success = false;
        $message = '';
        $status = 200;
        $result = [];

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = $request->getParsedBody();

            $concurso = Concurso::find((int) $body['IdConcurso']);
            if (!$concurso) {
                $status = 422;
                $message = 'Concurso no encontrado.';
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            $fecha_invitacion = $concurso->fecha_limite;
            $finalizacion_consultas = $concurso->finalizacion_consultas;
            $fecha_limite_economicas = $concurso->sobrecerrado ?? $concurso->fecha_limite_economicas;
            $ficha_tecnica_fecha_limite = $concurso->technical_includes ?? $concurso->ficha_tecnica_fecha_limite;
            $inicio_subasta = $concurso->is_online ?? $concurso->inicio_subasta;
            $hoy = Carbon::now();
            
            if ($fecha_invitacion < $hoy || $finalizacion_consultas < $hoy || ($concurso->sobrecerrado && $fecha_limite_economicas < $hoy) || ($concurso->technical_includes && $ficha_tecnica_fecha_limite < $hoy) || ($concurso->is_online && $inicio_subasta < $hoy)) {
                $status = 422;
                $message = 'Las fechas del concurso estan desactualizadas, debe actualizar las fechas.';
            } else {
                $creation = isset($body['IdUsuario']);


                if ($creation) {
                    $users = User::where('id', (int) $body['IdUsuario'])->get();
                } else {
                    if (!$concurso->oferentes) {
                        $status = 422;
                        $message = 'No se pudieron cargar los oferentes del concurso.';
                        $connection->rollBack();
                        return $this->json($response, [
                            'success' => false,
                            'message' => $message,
                        ], $status);
                    }
                    $companiesInvited = $concurso->oferentes->where('is_seleccionado', true)->pluck('id_offerer');
                    if (!$companiesInvited || $companiesInvited->count() == 0) {
                        $status = 422;
                        $message = 'No hay nuevos usuarios para enviar invitaciones.';
                    } else {
                        $companies = OffererCompany::with('users')->whereIn('id', $companiesInvited)->get();
                    }
                }

                // crear correo
                $title = 'Invitación a Concurso de Precios';
                $subject = $concurso->cliente->customer_company->business_name . ' - ' . $title;
                $template = rootPath(config('app.templates_path')) . '/email/invitation.tpl';
                $invitation_status = InvitationStatus::where('code', InvitationStatus::CODES['pending'])->first();
                if ($companiesInvited->count() > 0) {
                    foreach ($companies as $company) {
                        $users = $company->users->pluck('email');
                        if ($creation) {
                            $oferente = new Participante([
                                'id_offerer' => $company->id,
                                'id_concurso' => $concurso->id,
                                'etapa_actual' => Participante::ETAPAS['invitacion-pendiente']
                            ]);
                            $oferente->save();
                        } else {
                            $oferente = $concurso->oferentes->where('id_offerer', $company->id)->first();
                            $oferente->update([
                                'etapa_actual' => Participante::ETAPAS['invitacion-pendiente']
                            ]);
                            $oferente->refresh();
                        }
                        $invitation = new Invitation([
                            'concurso_id' => $concurso->id,
                            'participante_id' => $oferente->id,
                            'status_id' => $invitation_status->id
                        ]);
                        $invitation->save();

                        $html = $this->fetch($template, [
                            'title' => $title,
                            'ano' => Carbon::now()->format('Y'),
                            'concurso' => $concurso,
                            'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('d-m-Y H:i') : 'No aplica',
                            'company_name' => $company->business_name,
                            'timeZone' => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
                        ]);

                        $result = $emailService->send($html, $subject, $users, "");

                        $success = $result['success'];
                        if ($success) {
                            $connection->commit();
                            $message = 'Invitaciones enviadas con éxito.';
                        } else {
                            $connection->rollBack();
                            $message = 'Han ocurrido errores al enviar las invitaciones.';
                            break;
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
        ], $status);
    }

    public function sendReminder(Request $request, Response $response)
    {
        date_default_timezone_set(user()->customer_company->timeZone);
        $success = false;
        $message = '';
        $status = 200;
        $result = [];

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = $request->getParsedBody();
            $concurso = Concurso::find((int) $body['IdConcurso']);
            if (!$concurso) {
                $status = 422;
                $message = 'Concurso no encontrado.';
                $connection->rollBack();
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            if (!$concurso->oferentes) {
                $status = 422;
                $message = 'No se pudieron cargar los oferentes del concurso.';
                $connection->rollBack();
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            $oferente = $concurso->oferentes->where('id_offerer', (int) $body['idOfferer'])->first();
            if (!$oferente) {
                $status = 422;
                $message = 'Oferente no encontrado en este concurso.';
                $connection->rollBack();
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            $users = User::where('offerer_company_id', $oferente->id_offerer)->pluck('email');

            $invitation = $oferente->invitation;
            $invitation->update([
                'reminder' => true,
                'reminder_date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
            $invitation->save();
            $oferente->refresh();

            $title = 'Recordatorio Invitación a Concurso de Precios';
            $subject = $concurso->cliente->customer_company->business_name . ' - ' . $title;

            $template = rootPath(config('app.templates_path')) . '/email/invitation.tpl';
            $send = false;
            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('d-m-Y H:i') : 'No aplica',
                'company_name' => $oferente->company->business_name,
                'timeZone' => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
            ]);

            $result = $emailService->send($html, $subject, $users, "");

            $success = $result['success'];
            if ($success) {
                $send = true;
            } else {
                $send = false;
                $connection->rollBack();
                $message = 'Han ocurrido errores al enviar el recordatorio.';
            }
            if ($send) {
                $connection->commit();
                $message = 'Recordatorio enviado con éxito.';
            }

        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
        ], $status);
    }

    /**
     * Envía nuevamente las invitaciones que nunca llegaron a registrarse.
     *
     * Puede procesar un proveedor puntual (idOfferer) o todos los participantes
     * seleccionados del concurso que todavía no tengan una invitación.
     */
    public function retryUnsent(Request $request, Response $response)
    {
        date_default_timezone_set(user()->customer_company->timeZone);

        $body = $request->getParsedBody();
        $concurso = Concurso::with(['oferentes.invitation', 'oferentes.company.users'])
            ->find((int) ($body['IdConcurso'] ?? 0));

        if (!$concurso) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Concurso no encontrado.',
            ], 422);
        }

        $oferentes = $concurso->oferentes->filter(function ($oferente) {
            return !$oferente->invitation;
        });

        if (!empty($body['idOfferer'])) {
            $idOfferer = (int) $body['idOfferer'];
            $oferentes = $oferentes->filter(function ($oferente) use ($idOfferer) {
                return (int) $oferente->id_offerer === $idOfferer;
            });
        }

        if ($oferentes->count() === 0) {
            return $this->json($response, [
                'success' => false,
                'message' => 'No hay invitaciones sin enviar para procesar.',
            ], 422);
        }

        $emailService = new EmailService();
        $invitationStatus = InvitationStatus::where('code', InvitationStatus::CODES['pending'])->first();
        if (!$invitationStatus) {
            return $this->json($response, [
                'success' => false,
                'message' => 'No se encontró el estado pendiente para las invitaciones.',
            ], 422);
        }
        $title = 'Invitación a Concurso de Precios';
        $subject = $concurso->cliente->customer_company->business_name . ' - ' . $title;
        $template = rootPath(config('app.templates_path')) . '/email/invitation.tpl';
        $sent = 0;
        $failed = [];

        foreach ($oferentes as $oferente) {
            $company = $oferente->company;
            $recipients = $company ? $company->users->pluck('email') : collect();

            if (!$company || $recipients->count() === 0) {
                $failed[] = $company ? $company->business_name : ('Proveedor #' . $oferente->id_offerer);
                continue;
            }

            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('d-m-Y H:i') : 'No aplica',
                'company_name' => $company->business_name,
                'timeZone' => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
            ]);
            try {
                $result = $emailService->send($html, $subject, $recipients, '');
            } catch (\Exception $e) {
                $failed[] = $company->business_name;
                continue;
            }

            if (empty($result['success'])) {
                $failed[] = $company->business_name;
                continue;
            }

            $invitation = new Invitation([
                'concurso_id' => $concurso->id,
                'participante_id' => $oferente->id,
                'status_id' => $invitationStatus->id
            ]);
            $invitation->save();
            $oferente->update([
                'etapa_actual' => Participante::ETAPAS['invitacion-pendiente']
            ]);
            $sent++;
        }

        $success = $sent > 0;
        $message = $sent === 1
            ? 'Invitación enviada con éxito.'
            : $sent . ' invitaciones enviadas con éxito.';

        if (!$success) {
            $message = 'No se pudo enviar ninguna invitación. Proveedores con error: ' . implode(', ', $failed) . '.';
        } elseif (count($failed) > 0) {
            $message .= ' Quedaron pendientes por error: ' . implode(', ', $failed) . '.';
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'sent' => $sent,
                'failed' => count($failed),
                'partial' => $success && count($failed) > 0,
            ],
        ], $success ? 200 : 422);
    }

    public function filter(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $results = [];

        try {
            $user = user();
            $filters = json_decode($request->getParsedBody()['Filters']);

            $query = [];

            $companies = OffererCompany::query();

            $companies = $companies->whereHas('associated_customers', function ($query) use ($user) {
                $query->where('customer_id', $user->customer_company->id);
            })->whereHas('users');

            // Rubros
            if ($filters->Areas) {
                $companies = $companies->whereHas('areas', function ($query) use ($filters) {
                    $query->whereIn('area_id', $filters->Areas);
                });
            }

            // Obtengo todas las ciudades específicamente elegidas
            $cities = collect();
            if ($filters->Cities) {
                $cities = $cities->merge(
                    Ciudad::whereIn('id', $filters->Cities)
                        ->get()
                );
                if ($cities->count() > 0) {
                    $companies = $companies->whereHas('alcances', function ($query) use ($cities) {
                        $query->whereIn('id_ciudad', $cities->pluck('id')->toArray());
                    });
                }
            }

            // Obtengo todas las ciudades de provincias que no tengan ciudades específicamente elegidas
            $provinces = collect();
            if ($filters->Provinces) {
                $provinces = $provinces->merge(
                    Provincia::whereIn('id', $filters->Provinces)
                        ->whereDoesntHave('ciudades', function ($query) use ($filters) {
                            $query->whereIn('ciudades.id', $filters->Cities);
                        })
                        ->get()
                );
                if ($provinces->count() > 0) {
                    $companies = $companies->whereHas('alcances', function ($query) use ($provinces) {
                        $query->whereIn('id_provincia', $provinces->pluck('id')->toArray());
                    });
                }
            }

            // Obtengo todas las ciudades de países que no tengan provincias específicamente elegidas
            /*
            $countries = collect();
            if ($filters->Countries) {
            $countries = $countries->merge(
            Pais::whereIn('id', $filters->Countries)
            ->whereDoesntHave('provincias', function ($query) use ($filters) {
            $query->whereIn('provincias.id', $filters->Provinces);
            })
            ->get()
            );
            if ($countries->count() > 0) {
            $companies = $companies->whereHas('alcances', function ($query) use ($countries) {
            $query->whereIn('id_pais', $countries->pluck('id')->toArray());
            });
            }
            }
            */

            foreach ($companies->get() as $company) {
                $text = strtoupper($company->business_name);
                $text = $company->cuit ? $text . ', ' . $company->cuit : $text;
                $results[] = [
                    'id' => $company->id,
                    'text' => $text
                ];
            }

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'results' => $results,
            ]
        ], $status);
    }

    public function newInvitation(Request $request, Response $response)
    {
        date_default_timezone_set(user()->customer_company->timeZone);
        $success = false;
        $message = '';
        $status = 200;
        $result = [];
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = $request->getParsedBody();
            $concurso = Concurso::find((int) $body['IdConcurso']);
            if (!$concurso) {
                $status = 422;
                $message = 'Concurso no encontrado.';
                $connection->rollBack();
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            
            // Si el concurso ya tenía enviado el email de "todos cotizaron"
            // lo reseteamos porque se está agregando un nuevo proveedor
            $emailYaEnviado = $concurso->email_economica_enviado_at !== null;
            
            $oferente = new Participante([
                'id_offerer' => (int) $body['idOfferer'],
                'id_concurso' => $concurso->id,
                'etapa_actual' => Participante::ETAPAS['seleccionado']
            ]);
            $oferente->save();
            
            // Resetear el campo si ya se había enviado el email
            if ($emailYaEnviado) {
                $concurso->email_economica_enviado_at = null;
                $concurso->save();
                
                logger('concurso')->info(
                    "Se agregó nuevo proveedor (ID: {$body['idOfferer']}) al concurso {$concurso->id}. " .
                    "Campo email_economica_enviado_at reseteado a NULL para permitir nuevo envío de email."
                );
            }
            
            $concurso->refresh();
            if (!$concurso->oferentes) {
                $status = 422;
                $message = 'No se pudieron cargar los oferentes del concurso.';
                $connection->rollBack();
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            $companiesInvited = $concurso->oferentes->where('is_seleccionado', true)->pluck('id_offerer');
            if (!$companiesInvited || $companiesInvited->count() == 0) {
                $status = 422;
                $message = 'No hay oferentes seleccionados.';
                $connection->rollBack();
                return $this->json($response, [
                    'success' => false,
                    'message' => $message,
                ], $status);
            }
            $companies = OffererCompany::with('users')->whereIn('id', $companiesInvited)->get();
            // dd($companiesInvited);  

            // crear correo
            $title = 'Invitación a Concurso de Precios';
            $subject = $concurso->cliente->customer_company->business_name . ' - ' . $title;
            $template = rootPath(config('app.templates_path')) . '/email/invitation.tpl';
            $invitation_status = InvitationStatus::where('code', InvitationStatus::CODES['pending'])->first();
            if ($companiesInvited->count() > 0) {
                foreach ($companies as $company) {
                    $users = $company->users->pluck('email');
                    $oferente = $concurso->oferentes->where('id_offerer', $company->id)->first();
                    $oferente->update([
                        'etapa_actual' => Participante::ETAPAS['invitacion-pendiente']
                    ]);
                    $oferente->refresh();
                    $invitation = new Invitation([
                        'concurso_id' => $concurso->id,
                        'participante_id' => $oferente->id,
                        'status_id' => $invitation_status->id
                    ]);
                    $invitation->save();

                    $html = $this->fetch($template, [
                        'title' => $title,
                        'ano' => Carbon::now()->format('Y'),
                        'concurso' => $concurso,
                        'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('d-m-Y H:i') : 'No aplica',
                        'company_name' => $company->business_name,
                        'timeZone' => $this->toGmtOffset($concurso->cliente->customer_company->timeZone)
                    ]);

                    $result = $emailService->send($html, $subject, $users, "");


                    $success = $result['success'];
                    if ($success) {
                        $connection->commit();
                        $message = 'Invitaciones enviadas con éxito.';
                    } else {
                        $connection->rollBack();
                        $message = 'Han ocurrido errores al enviar las invitaciones.';
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
        ], $status);
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
}
