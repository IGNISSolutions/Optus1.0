<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserType;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Concurso;
use App\Models\Mensaje;
use App\Services\EmailService;
use Carbon\Carbon;
use \Exception as Exception;

class ChatController extends BaseController
{
    public function list(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $chat_enabled = false;
        $proveedores = [];

        try {
            //throw new Exception('Prueba FD.', 500);

            $body = json_decode($request->getParsedBody()['Data']);
            $tipo = isset($body->tipo) ? $body->tipo : null;
            $categoria = isset($body->categoria) ? $body->categoria : null;
            $estado = isset($body->estado) ? $body->estado : null;
            $tiposPreguntas = isset($body->tiposPreguntas) ? $body->tiposPreguntas : null;
            $concurso = Concurso::find((int) $body->IdConcurso);
            $messages = $concurso->mensajes()->userType()->where('parent', 0)->tipo($tipo)->categoria($categoria)->estado($estado)->tiposPreguntas($tiposPreguntas)->get();
            $user = user();
            $datosConcurso = [
                'idConcurso' => $concurso->id,
                'nombre' => $concurso->nombre,
                'cliente' => $concurso->cliente->customer_company->business_name,
                'comprador' => $concurso->cliente->full_name,
                'muroTipo' => $user->type->description
            ];

            foreach ($messages as $item) {
                
                $answerRead = true;
                $respuestas = [];
                $resps = $item->where('parent', $item->id)->get();
                if (count($resps) > 0) {

                    foreach ($resps as $resp) {
                        if (cannot('chat-admin')) {
                            if ($resp->usuario->id !== $user->id && !$resp->is_approved) {
                                continue;
                            }
                        }

                        // Solo el autor puede ver los comentarios rechazados
                        if ($resp->usuario->id !== $user->id && $resp->is_rejected) {
                            continue;
                        }

                        $respuestas[] = [
                            'id' => $resp->id,
                            'UserId' => $resp->usuario->id,
                            'usuario' => $resp->usuario->type->isOfferer ? $resp->usuario->offerer_company->business_name : $resp->usuario->customer_company->business_name,
                            'usuario_imagen' => $resp->usuario->image,
                            'concurso' => $resp->concurso->id,
                            'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $resp->fecha)->diffForHumans(),
                            'mensaje' => $resp->mensaje,
                            'estado' => $resp->estado,
                            'is_admin' => $resp->usuario->can('chat-admin'),
                            'tipo_name' => $resp->usuario->type->description,
                            'padre' => $resp->parent,
                            'filename' => $resp->filename,
                            'date' => Carbon::createFromFormat('Y-m-d H:i:s', $resp->fecha)->format('d-m-Y'),
                        ];
                        $answerRead = $resp->user_read;
                    }
                }

                // Si no es admin, no podrá ver los comentarios pendientes que no sean suyos
                if (cannot('chat-admin')) {
                    if ($item->usuario->id !== $user->id && !$item->is_approved) {
                        continue;
                    }
                }

                // Solo el autor puede ver los comentarios rechazados
                if ($item->usuario->id !== $user->id && $item->is_rejected) {
                    continue;
                }

                $list[] = [
                    'id' => $item->id,
                    'UserId' => $item->usuario->id,
                    'usuario' => $item->usuario->type->isOfferer ? $item->usuario->offerer_company->business_name : $item->usuario->customer_company->business_name,
                    'usuario_imagen' => $item->usuario->image,
                    'concurso' => $item->concurso->id,
                    'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $item->fecha)->diffForHumans(),
                    'mensaje' => $item->mensaje,
                    'estado' => $item->estado,
                    'is_admin' => $item->usuario->can('chat-admin'),
                    'tipo_name' => $item->usuario->type->description,
                    'tipo_pregunta' => $item->tipo_mensaje,
                    'respuestas' => $respuestas,
                    'respondida' => count($respuestas) > 0 ? 'Si' : 'No',
                    'filename' => $item->filename,
                    'messageRead' => $item->user_read,
                    'answerRead' => $answerRead,
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s', $item->fecha)->format('d-m-Y'),
                    'to' => $item->to == 0 ? 'Todos' : $item->messageTo->company->business_name
                ];
            }

            // Valida si se debe mostrar o no el input del Muro de Consultas
            if (isOfferer()) {
                $oferente = $concurso->oferentes->where('id_offerer', (int) $user->offerer_company_id)->first();
                $chat_enabled = $oferente->is_chat_enabled;
            } else {
                $chat_enabled = $concurso->is_chat_enabled;
                $participantes = $concurso->participantesFiltradosPorEtapa()->get();
                if (count($participantes) > 0) {
                    foreach ($participantes as $prov)
                        $proveedores[] = [
                            "id" => $prov->id_offerer,
                            "text" => $prov->company->business_name
                        ];
                }
            }

            $success = true;
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'concurso' => $datosConcurso,
                'list' => $list,
                'enabled' => $chat_enabled,
                'filepath' => filePath($concurso->file_path),
                'proveedores' => $proveedores
            ]
        ], $status);
    }

   public function check(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $has_new_messages = false;

        try {
            $body = json_decode($request->getParsedBody()['Data']);
            $user = user();
            $concurso = Concurso::find((int) $body->IdConcurso);

            $mensajes = isCustomer()
            ? $concurso->mensajes
            : $concurso->mensajes->where('is_approved', true);



            if ($mensajes && $mensajes->count() > 0) {
                $last_message = $mensajes->last();
                $users_read = $last_message->users_read;
                $has_new_messages = $users_read->where('id', $user->id)->count() === 0;
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
                'new_messages' => $has_new_messages,
            ]
        ], $status);
    }

    public function store(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $is_pregunta = null;
        $pregunta_id = null;
        $is_respuesta = null;
        $is_parent = null;

        $status = 200;
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = json_decode($request->getParsedBody()['Data']);



            $concurso = Concurso::find((int) $body->IdConcurso);
            $file = isset($body->Message->file->filename) ? $body->Message->file->filename : null;

            $user = user();
            $tipo = array_key_exists('Tipo', $body) ? $body->Tipo : null;
            $parent = array_key_exists('Parent', $body) ? $body->Parent : 0;
            $proveedor = isset($body->Proveedor) ? $body->Proveedor : null;
            if($parent){
                $message = Mensaje::find($parent);
                $proveedor = $message->to != 0 ? $message->to : null ;
            }


            $new = [
                'usr_id' => $user->id,
                'cso_id' => $concurso->id,
                'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
                'mensaje' => $body->Message->message,
                'estado' => can('chat-admin') ? '1' : '2',
                'leido' => $user->id,
                'parent' => $parent,
                'tipo' => $tipo,
                'filename' => $file,
                'to' => $proveedor ? $proveedor : 0
            ];
            $new_message = new Mensaje($new);
            $new_message->save();
            $is_pregunta = $parent === 0 ? true : false;
            $is_respuesta = $parent === 0 ? false : true;
            $pregunta_id = $new_message->id;
            $respuesta_id = $new_message->parent;


            if (cannot('chat-admin')) {
                $result = $this->sendEmailsCustomers($concurso, $user, $emailService, $tipo);
                $success = $result['success'];
                $message = $result['message'];
            } else {
                if (!$proveedor) {
                    $result = $this->sendEmailsOfferer($concurso, $user, $emailService, $new_message, $is_pregunta);
                    $success = $result['success'];
                    $message = $result['message'];
                } else {
                    $result = $this->sendEmailOfferer($concurso, $user, $emailService, $new_message, $is_pregunta, $proveedor);
                    $success = $result['success'];
                    $message = $result['message'];
                }
            }

            if ($success) {
                $connection->commit();
            } else {
                $connection->rollBack();
            }
        } catch (Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'is_pregunta' => $is_pregunta,
            'is_respuesta' => $is_respuesta,
            'pregunta_id' => $pregunta_id,
            'respuesta_id' => $respuesta_id
        ], $status);
    }

    public function approveOrReject(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = json_decode($request->getParsedBody()['Data']);
            $concurso = Concurso::find((int) $body->IdConcurso);
            $mensaje = $concurso->mensajes->find((int) $body->IdMessage);


            $action = $body->Action;
            $user = user();

            switch ($action) {
                case 'approve':
                    $estado = 1;
                    break;
                case 'reject';
                    $estado = 3;
                    break;
                default:
                    $estado = 0;
                    break;
            }

            $mensaje->update([
                'estado' => $estado
            ]);
            $mensaje->refresh();

            $title = 'Moderación Comentario Muro';
            $subject = $concurso->nombre . ' - ' . $title;

            $template = rootPath(config('app.templates_path')) . '/email/chat-approve.tpl';

            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'message' => $mensaje,
                'oferente' => User::find($mensaje->usr_id),
                'user' => $mensaje->usuario
            ]);

            $result = $emailService->send($html, $subject, [$mensaje->usuario->email], $mensaje->usuario->full_name);
            $success = $result['success'];
            $message = $result['message'];

            if ($success) {
                $connection->commit();
            } else {
                $connection->rollBack();
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
            'parent' => $mensaje->parent == 0 ? $mensaje->id : $mensaje->parent,
            'tipo' => $mensaje->parent == 0 ? 'pregunta' : 'respuesta'
        ], $status);
    }

    public function toggleRead(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $body = json_decode($request->getParsedBody()['Data']);
            $concurso = Concurso::find((int) $body->IdConcurso);
            $user = user();

            $mensaje =
                isCustomer() ?
                $concurso->mensajes->find($body->Message->id) :
                $concurso->mensajes->where('is_approved', true)->find($body->Message->id);
            if ($mensaje->users_read->where('id', $user->id)->count() === 0) {
                $mensaje->update([
                    'leido' => $mensaje->leido . ',' . $user->id
                ]);
            }

            $respuestas =
                isCustomer() ?
                $concurso->mensajes->where('parent', $body->Message->id) :
                $concurso->mensajes->where('is_approved', true)->where('parent', $body->Message->id);

            if ($respuestas->count() > 0) {
                foreach ($respuestas as $item) {
                    if ($item->users_read->where('id', $user->id)->count() === 0) {
                        $item->update([
                            'leido' => $item->leido . ',' . $user->id
                        ]);
                    }
                }
            }

            $success = true;
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }

    private function sendEmailsCustomers($concurso, $user, $emailService, $tipo)
    {
        $type = $tipo === 'tecnica' ? 'Técnica' : 'Comercial';
        $aprobadores = $concurso->usuario_califica_reputacion ? explode(',', $concurso->usuario_califica_reputacion) : null;
        $emails = [];
        if ($aprobadores) {
            $emails = array_map([$this, 'getEmailUser'], $aprobadores);
        } else {
            $emails[] = $concurso->cliente->email;
        }
        if (!in_array($concurso->cliente->email, $emails)) {
            $emails[] = $concurso->cliente->email;
        }
        $title = 'Nueva consulta en muro de tipo ' . $type;
        $subject = $concurso->nombre . ' - ' . $title;

        $template = rootPath(config('app.templates_path')) . '/email/chat-new.tpl';

        $html = $this->fetch($template, [
            'title' => $title,
            'ano' => Carbon::now()->format('Y'),
            'concurso' => $concurso,
            'user' => $user
        ]);

        $result = $emailService->send($html, $subject, $emails, $concurso->cliente->full_name);
        return $result;
    }

    private function sendEmailsOfferer($concurso, $user, $emailService, $new_message, $is_pregunta)
    {

        $oferentes = $concurso->oferentes->where('has_invitacion_aceptada')->where('is_concurso_rechazado', false);
        if (count($oferentes) == 0) {
            return [
                'message' => '',
                'status' => 200,
                'success' => true,
            ];
        }

        $result = [];
        foreach ($oferentes as $oferente) {
            $emails = [];
            $emails = $oferente->company->users->pluck('email');
            $title = $is_pregunta ? 'Mensaje nuevo Muro de consultas Optus' : 'Respuesta Muro Optus';
            $subject = $concurso->nombre . ' - ' . $title;

            $template = $is_pregunta ? rootPath(config('app.templates_path')) . '/email/chat-customer.tpl': rootPath(config('app.templates_path')) . '/email/chat-customer-response.tpl';

            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'user' => $user,
                'company_name' => $oferente->company->business_name,
                'mensaje' => $new_message
            ]);
            $result = $emailService->send($html, $subject, $emails, $concurso->cliente->full_name);

            if (!$result['success']) {
                $result = [
                    'message' => 'Ha ocurrido un error al intentar enviar los correos.',
                    'status' => 500,
                    'success' => false,
                ];
                break;
            }
        }

        return $result;
    }

    public function getEmailUser($user)
    {
        return User::find($user)->email;
    }

    public function getMessage(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = null;
        $chat_enabled = true;


        try {
            //throw new Exception('Prueba FD.', 500);

            $body = json_decode($request->getParsedBody()['Data']);
            $concurso = Concurso::find((int) $body->IdConcurso);
            $selectedMessage = $concurso->mensajes->find($body->parent_id);
            $user = user();
            $respuestas = [];
            $resps = $selectedMessage->where('parent', $selectedMessage->id)->get();
            if (count($resps) > 0) {
                foreach ($resps as $resp) {
                    if (cannot('chat-admin')) {
                        if ($resp->usuario->id !== $user->id && !$resp->is_approved) {
                            continue;
                        }
                    }

                    // Solo el autor puede ver los comentarios rechazados
                    if ($resp->usuario->id !== $user->id && $resp->is_rejected) {
                        continue;
                    }

                    $respuestas[] = [
                        'id' => $resp->id,
                        'UserId' => $resp->usuario->id,
                        'usuario' => $resp->usuario->type->isOfferer ? $resp->usuario->offerer_company->business_name : $resp->usuario->customer_company->business_name,
                        'usuario_imagen' => $resp->usuario->image,
                        'concurso' => $resp->concurso->id,
                        'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $resp->fecha)->diffForHumans(),
                        'mensaje' => $resp->mensaje,
                        'estado' => $resp->estado,
                        'is_admin' => $resp->usuario->can('chat-admin'),
                        'tipo_name' => $resp->usuario->type->description,
                        'padre' => $resp->parent,
                        'filename' => $resp->filename
                    ];
                }
            }

            $list = [
                'id' => $selectedMessage->id,
                'UserId' => $selectedMessage->usuario->id,
                'usuario' => $selectedMessage->usuario->first_name,
                'usuario_imagen' => $selectedMessage->usuario->image,
                'concurso' => $selectedMessage->concurso->id,
                'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $selectedMessage->fecha)->diffForHumans(),
                'mensaje' => $selectedMessage->mensaje,
                'estado' => $selectedMessage->estado,
                'is_admin' => $selectedMessage->usuario->can('chat-admin'),
                'tipo_name' => $selectedMessage->usuario->type->description,
                'tipo_pregunta' => $selectedMessage->tipo_mensaje,
                'respuestas' => $respuestas,
                'respondida' => count($respuestas) > 0 ? 'Si' : 'No',
                'filename' => $selectedMessage->filename
            ];

            // Valida si se debe mostrar o no el input del Muro de Consultas
            if (isOfferer()) {
                $oferente = $concurso->oferentes->where('id_offerer', (int) $user->offerer_company_id)->first();
                $chat_enabled = $oferente->is_chat_enabled;
            }

            $success = true;
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'selectedMessage' => $list,
                'enabled' => $chat_enabled,
                'filepath' => filePath($concurso->file_path)
            ]
        ], $status);
    }

    private function sendEmailOfferer($concurso, $user, $emailService, $new_message, $is_pregunta, $oferente)
    {

        $oferente = $concurso->oferentes->where('id_offerer', $oferente)->where('has_invitacion_aceptada')->where('is_concurso_rechazado', false)->first();
        if (!$oferente) {
            return [
                'message' => '',
                'status' => 200,
                'success' => true,
            ];
        }

        $result = [];

        $emails = [];
        $emails = $oferente->company->users->pluck('email');
        $title = $is_pregunta ? 'Mensaje nuevo Muro de consultas Optus' : 'Respuesta Muro Optus';
        $subject = $concurso->nombre . ' - ' . $title;

        $template = $is_pregunta ? rootPath(config('app.templates_path')) . '/email/chat-customer.tpl': rootPath(config('app.templates_path')) . '/email/chat-customer-response.tpl';

        $html = $this->fetch($template, [
            'title' => $title,
            'ano' => Carbon::now()->format('Y'),
            'concurso' => $concurso,
            'user' => $user,
            'company_name' => $oferente->company->business_name,
            'mensaje' => $new_message
        ]);
        $result = $emailService->send($html, $subject, $emails, $concurso->cliente->full_name);

        if (!$result['success']) {
            $result = [
                'message' => 'Ha ocurrido un error al intentar enviar los correos.',
                'status' => 500,
                'success' => false,
            ];
        }


        return $result;
    }
}
