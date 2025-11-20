<?php

namespace App\Http\Controllers\Proposal;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Concurso;
use App\Models\Step;
use App\Models\Participante;
use App\Models\ParticipanteGoDocument;
use App\Models\Proposal;
use App\Models\ProposalType;
use App\Models\ProposalStatus;
use App\Models\ProposalDocument;
use App\Services\EmailService;
use Carbon\Carbon;

class EconomicProposalController extends BaseController
{
    public function send(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];
        $redirect_url = null;

        try {
            $body = json_decode($request->getParsedBody()['Data']);

            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $user = user();
            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

            $condicionPago = $concurso->condicion_pago == 'si'
                ? (isset($body->EconomicProposal->CondicionPago) ? $body->EconomicProposal->CondicionPago : '')
                : Participante::CONDICIONES_PAGO[0]['id'];

            $fields = [
                'comment' => $body->EconomicProposal->comment,
                'payment_deadline' => isset($body->EconomicProposal->PlazoPago) ? $body->EconomicProposal->PlazoPago : '',
                'payment_condition' => $condicionPago,
                'values' => array_map(function ($item) use ($user) {
                    return [
                        'producto' => $item->product_id,
                        'cotizacion' => $item->cotizacion,
                        'cantidad' => $item->cantidad,
                        'fecha' => $item->fecha,
                        'unidad' => $item->measurement_name,
                        'id_offerer' => $user->offerer_company_id,
                        // persistimos el estado del switch si viene del front
                        'selected' => isset($item->ProductSelected) ? (bool) $item->ProductSelected : (isset($item->selected) ? (bool)$item->selected : null),
                    ];
                }, $body->EconomicProposal->values)
            ];

            // ===== Validación =====
            $validation_fields = $fields;
            $validation_fields['values'] = array_filter($fields['values'], function ($item) use ($concurso) {
                if ($concurso->is_go) {
                    return ($item['cotizacion'] && !empty($item['cotizacion'])) ||
                        ($item['cantidad'] && !empty($item['cantidad']));
                } else {
                    return ($item['cotizacion'] && !empty($item['cotizacion'])) ||
                        ($item['cantidad'] && !empty($item['cantidad'])) ||
                        ($item['fecha'] && !empty($item['fecha']));
                }
            });

            $validator = $this->validate($body, $concurso, $validation_fields);

            if ($validator->fails()) {
                $success = false;
                $message = $validator->errors()->first();
                $status = 422;
            } else {
                // ===== Misma ronda: elegir versión a ENVIAR y soft-delete del resto =====
                $proposal_type = ProposalType::where('code', ProposalType::CODES['economic'])->first();
                $pendingStatus = ProposalStatus::where('code', ProposalStatus::CODES['pending'])->first();

                // Traer todas las propuestas activas (no borradas) de esta ronda
                $allForRound = Proposal::where('participante_id', $oferente->id)
                    ->where('type_id', $proposal_type->id)
                    ->where('numero_ronda', $concurso->ronda_actual)
                    ->whereNull('deleted_at')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                // Elegir cuál enviar: si viene id desde el front, usarlo; si no, la más reciente
                $economic_proposal = null;
                if (isset($body->EconomicProposal->id)) {
                    $economic_proposal = $allForRound->firstWhere('id', intval($body->EconomicProposal->id));
                }
                if (!$economic_proposal) {
                    $economic_proposal = $allForRound->first(); // la más reciente
                }

                // Si no hay ninguna activa, crear una nueva con numero_ronda
                if (!$economic_proposal) {
                    $economic_proposal = new Proposal([
                        'participante_id' => $oferente->id,
                        'status_id' => $pendingStatus->id,
                        'type_id' => $proposal_type->id,
                        'numero_ronda' => $concurso->ronda_actual
                    ]);
                    $economic_proposal->save();
                    $economic_proposal->refresh();
                }

                // Actualizar datos de la propuesta elegida para ENVIAR (sin cambiar status si no tenés un "submitted")
                $fields['values'] = json_encode($fields['values']);
                $economic_proposal->update($fields);

                // Soft-delete del resto (misma ronda) para que quede SOLO UNA activa
                Proposal::where('participante_id', $oferente->id)
                    ->where('type_id', $proposal_type->id)
                    ->where('numero_ronda', $concurso->ronda_actual)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $economic_proposal->id)
                    ->update(['deleted_at' => Carbon::now()]);

                // Etapa del participante
                $oferente->update([
                    'etapa_actual' => $concurso->adjudicacion_anticipada && $concurso->alguno_revisado
                        ? Participante::ETAPAS['economica-revisada']
                        : Participante::ETAPAS['economica-presentada']
                ]);
                $oferente->refresh();

                // Adjuntos
                $result = $this->updateDocuments($concurso, $oferente, $body, $economic_proposal);

                if ($result['success']) {
                    // Emails
                    $template1 = rootPath(config('app.templates_path')) . '/email/economic-send.tpl';
                    $message1 = $this->fetch($template1, [
                        'concurso' => $concurso,
                        'title' => 'Propuesta Económica',
                        'ano' => Carbon::now()->format('Y'),
                        'cliente' => $concurso->cliente->customer_company->business_name,
                        'proveedor' => $user->offerer_company->business_name,
                        'hora' => Carbon::now()->subHours(3)->format('d/m/Y H:i:s'),
                    ]);

                    $template2 = rootPath(config('app.templates_path')) . '/email/economic-confirmation.tpl';
                    $message2 = $this->fetch($template2, [
                        'concurso' => $concurso,
                        'title' => 'Confirmacion propuesta economica',
                        'ano' => Carbon::now()->format('Y'),
                        'cliente' => $concurso->cliente->customer_company->business_name,
                        'proveedor' => $user->offerer_company->business_name,
                    ]);

                    $emails = [
                        [
                            'message' => $message1,
                            'subject' => $concurso->nombre . ' - Propuesta Económica',
                            'email_to' => [$concurso->cliente->email],
                            'alias' => '',
                        ],
                        [
                            'message' => $message2,
                            'subject' => $concurso->nombre . ' - Confirmacion propuesta economica',
                            'email_to' => [$user->email],
                            'alias' => '',
                        ]
                    ];

                    $results = $emailService->sendMultiple($emails);

                    foreach ($results as $res) {
                        if (!$res['success']) {
                            $success = false;
                            $message = $res['message'];
                            $status = 422;
                            break;
                        }
                    }

                    if (!isset($message)) {
                        $success = true;
                    }
                } else {
                    $success = false;
                    $message = $result['message'];
                    $status = 422;
                }
            }

            if ($success) {
                $message = $concurso->is_go ? 'Cotización enviada con éxito.' : 'Propuesta enviada con éxito.';
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
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }



    public function update(Request $request, Response $response)
    {
        // Guardar sin enviar (borrador) — SIN VALIDACIONES
        $success = false;
        $message = null;
        $status = 200;
        $result = [];
        $redirect_url = null;

        try {
            $body = json_decode($request->getParsedBody()['Data']);

            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

            $condicionPago = $concurso->condicion_pago == 'si'
                ? (isset($body->EconomicProposal->CondicionPago) ? $body->EconomicProposal->CondicionPago : '')
                : Participante::CONDICIONES_PAGO[0]['id'];

            // Tomar lo que venga (permitir nulos/vacíos)
            $values = array_map(function ($item) use ($user) {
                return [
                    'producto' => isset($item->product_id) ? $item->product_id : null,
                    'cotizacion' => isset($item->cotizacion) ? $item->cotizacion : null,
                    'cantidad' => isset($item->cantidad) ? $item->cantidad : null,
                    'fecha' => isset($item->fecha) ? $item->fecha : null,
                    'unidad' => isset($item->measurement_name) ? $item->measurement_name : null,
                    'id_offerer' => $user->offerer_company_id,
                    // 👇 NUEVO: persistimos el switch
                    'selected' => isset($item->ProductSelected) ? (bool) $item->ProductSelected : (isset($item->selected) ? (bool)$item->selected : null)
                ];
            }, isset($body->EconomicProposal->values) ? $body->EconomicProposal->values : []);

            $fields = [
                'comment' => isset($body->EconomicProposal->comment) ? $body->EconomicProposal->comment : '',
                'payment_deadline' => isset($body->EconomicProposal->PlazoPago) ? $body->EconomicProposal->PlazoPago : '',
                'payment_condition' => $condicionPago,
                'values' => $values
            ];

            // Crear SIEMPRE un nuevo borrador (pending) en la misma ronda
            $proposal_type = ProposalType::where('code', ProposalType::CODES['economic'])->first();
            $pendingStatus = ProposalStatus::where('code', ProposalStatus::CODES['pending'])->first();

            $economic_proposal = new Proposal([
                'participante_id' => $oferente->id,
                'status_id' => $pendingStatus->id,
                'type_id' => $proposal_type->id,
                'numero_ronda' => $concurso->ronda_actual,
                'comment' => $fields['comment'],
                'payment_deadline' => $fields['payment_deadline'],
                'payment_condition' => $fields['payment_condition'],
                'values' => json_encode($fields['values']),
            ]);
            $economic_proposal->save();
            $economic_proposal->refresh();
            $oferente->refresh();

            // Adjuntos: modo NO ESTRICTO
            $result = $this->updateDocuments($concurso, $oferente, $body, $economic_proposal, false);
            if (!$result['success']) {
                // En guardado parcial NO bloqueamos por faltantes de adjuntos
                // Podés loguear si querés: logger()->warning('Guardar parcial económica', $result);
            }

            $success = true;

            if ($success) {
                $connection->commit();
                $message = $concurso->is_go ? 'Cotización guardada parcialmente.' : 'Propuesta guardada parcialmente.';
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
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }


    public function auctionCotizar(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

            $errors = $this->auctionValidateItem($concurso, $body);

            if (!$errors) {
                // Si estamos en tiempo de descuento, agregamos tiempo extra.
                if ($concurso->countdown < 60 && $concurso->tiempo_adicional && $concurso->tiempo_adicional > 0) {
                    $concurso->setAdditionalTime($concurso->tiempo_adicional);
                    $concurso->update();
                }

                $h1 = json_decode(json_encode($body->Items), true);

                // Obtener valores anteriores.
                $economic_proposal = $oferente->economic_proposal;
                $ofertas_old = $economic_proposal ? $economic_proposal->values : [];
                $ofertas_new = [];

                // Crear nuevos registros.
                $i = 0;
                foreach ($concurso->productos as $producto) {
                    $oferta_new = [
                        'producto' => $producto->id,
                        'unidad' => $producto->unidad_medida->name,
                        'cotizacion' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['cotizacion']) ? $ofertas_old[$i]['cotizacion'] : null,
                        'creado' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['creado']) ? $ofertas_old[$i]['creado'] : null,
                        'fecha' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['fecha']) ? $ofertas_old[$i]['fecha'] : null,
                        'cantidad' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['cantidad']) ? $ofertas_old[$i]['cantidad'] : null,
                        'anulada' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['anulada']) ? $ofertas_old[$i]['anulada'] : false,
                        // preservamos el estado del switch si estaba presente
                        'selected' => isset($ofertas_old[$i]) && array_key_exists('selected', $ofertas_old[$i]) ? (bool)$ofertas_old[$i]['selected'] : null
                    ];

                    if ($i == $body->Index) {
                        $oferta_new['cotizacion'] = $h1[$i]['valores']['cotizacion'];
                        $oferta_new['cantidad'] = $h1[$i]['valores']['cantidad'];
                        $oferta_new['creado'] = Carbon::now()->format('Y-m-d H:i:s');
                        $oferta_new['anulada'] = false;
                    }

                    $ofertas_new[] = $oferta_new;

                    $i++;
                }
                // Sumar valores históricos a la cadena.
                $ofertas_new = array_merge($ofertas_new, $ofertas_old);
                $economic_values = json_encode($ofertas_new);

                if ($economic_proposal) {
                    $economic_proposal->update([
                        'values' => $economic_values
                    ]);
                } else {
                    $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['pending'])->first();
                    $proposal_type = ProposalType::where('code', ProposalType::CODES['economic'])->first();
                    $economic_proposal = new Proposal([
                        'participante_id' => $oferente->id,
                        'status_id' => $proposal_status->id,
                        'type_id' => $proposal_type->id,
                        'values' => $economic_values
                    ]);
                    $economic_proposal->save();
                }

                $oferente->update([
                    'etapa_actual' => Participante::ETAPAS['economica-presentada']
                ]);

                $message = 'La subasta ha sido modificada con éxito.';
                $success = true;
            } else {
                $message = $errors[0];
                $success = false;
                $status = 422;
            }

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
            'message' => $message
        ], $status);
    }

    public function auctionAnular(Request $request, Response $response)
    {
        $success = false;
        $error = false;
        $message = null;
        $status = 200;

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

            $productos = $concurso->getSubastaOutputByUser()['Items'];

            $existe = isset($productos[$body->Index]) && $productos[$body->Index]['valores']['cotizacion'];
            $permite_anular = $concurso->permitir_anular_oferta == 'si' ? true : false;
            $fecha_creacion = $existe && $productos[$body->Index]['valores']['creado'] ? Carbon::createFromFormat('Y-m-d H:i:s', $productos[$body->Index]['valores']['creado']) : null;

            // Verificamos que la oferta exista en DDBB.
            if (!$existe) {
                $message = 'Oferta inexistente.';
                $error = true;
                $status = 422;
            }

            // Verificamos que la oferta sea anulable y que no tenga más de 1 minuto de creada.
            if (!$permite_anular) {
                $message = 'Las ofertas no pueden anularse en este concurso.';
                $error = true;
                $status = 422;
            }

            if ($fecha_creacion && $fecha_creacion->diffInSeconds(Carbon::now()) > 60) {
                $message = 'Su oferta ya lleva más de 1 minuto publicada y no puede anularse.';
                $error = true;
                $status = 422;
            }

            if (!$error) {
                // Si estamos en tiempo de descuento, agregamos tiempo extra.
                if ($concurso->countdown < 60 && $concurso->tiempo_adicional && $concurso->tiempo_adicional > 0) {

                    $concurso->setAdditionalTime($concurso->tiempo_adicional);
                    $concurso->update();
                }

                // Obtener valores anteriores.
                $economic_proposal = $oferente->economic_proposal;
                $ofertas_old = $economic_proposal ? $economic_proposal->values : [];
                $ofertas_new = [];

                // Crear nuevos registros.
                $i = 0;
                foreach ($concurso->productos as $producto) {
                    $oferta_new = [
                        'producto' => $producto->id,
                        'unidad' => $producto->unidad_medida->name,
                        'cotizacion' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['cotizacion']) ? $ofertas_old[$i]['cotizacion'] : null,
                        'creado' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['creado']) ? $ofertas_old[$i]['creado'] : null,
                        'fecha' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['fecha']) ? $ofertas_old[$i]['fecha'] : null,
                        'cantidad' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['cantidad']) ? $ofertas_old[$i]['cantidad'] : null,
                        'anulada' => isset($ofertas_old[$i]) && isset($ofertas_old[$i]['anulada']) ? $ofertas_old[$i]['anulada'] : null,
                        'selected' => isset($ofertas_old[$i]) && array_key_exists('selected', $ofertas_old[$i]) ? (bool)$ofertas_old[$i]['selected'] : null
                    ];

                    if ($i == $body->Index) {
                        $oferta_new['cotizacion'] = null;
                        $oferta_new['cantidad'] = null;
                        $oferta_new['anulada'] = true;
                        $oferta_new['creado'] = Carbon::now()->format('Y-m-d H:i:s');
                    }

                    $ofertas_new[] = $oferta_new;
                    $i++;
                }

                // Sumar valores históricos a la cadena.
                $ofertas_new = array_merge($ofertas_new, $ofertas_old);

                $etapa_actual = Participante::ETAPAS['economica-pendiente'];
                $economic_values = null;
                foreach ($ofertas_new as $oferta) {
                    if (!$oferta['anulada']) {
                        $economic_values = json_encode($ofertas_new);
                        $etapa_actual = Participante::ETAPAS['economica-presentada'];
                        break;
                    }
                }

                if ($economic_proposal) {
                    $economic_proposal->update([
                        'values' => $economic_values
                    ]);
                } else {
                    $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['pending'])->first();
                    $proposal_type = ProposalType::where('code', ProposalType::CODES['economic'])->first();
                    $economic_proposal = new Proposal([
                        'participante_id' => $oferente->id,
                        'status_id' => $proposal_status->id,
                        'type_id' => $proposal_type->id,
                        'values' => $economic_values
                    ]);
                    $economic_proposal->save();
                }

                $oferente->update([
                    'etapa_actual' => $etapa_actual
                ]);

                $success = true;
            }

            if ($success) {
                $connection->commit();
                $message = 'La subasta ha sido modificada con éxito.';
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
            'message' => $message
        ], $status);
    }

    public function auctionUpdate(Request $request, Response $response)
    {
        $success = true;
        $message = null;
        $status = 200;

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

            $economic_proposal = $oferente->economic_proposal;
            if ($economic_proposal) {
                $economic_proposal->update([
                    'comment' => $body->EconomicProposal->comment
                ]);
            } else {
                $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['pending'])->first();
                $proposal_type = ProposalType::where('code', ProposalType::CODES['economic'])->first();
                $economic_proposal = new Proposal([
                    'participante_id' => $oferente->id,
                    'status_id' => $proposal_status->id,
                    'type_id' => $proposal_type->id,
                    'comment' => $body->EconomicProposal->comment
                ]);
                $economic_proposal->save();
            }

            $oferente->refresh();
            $result = $this->updateDocuments($concurso, $oferente, $body, $economic_proposal);

            if ($success) {
                $connection->commit();
                $message = 'La subasta ha sido modificada con éxito.';
            } else {
                $connection->rollBack();
                $message = $result['message'];
            }

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

    private function auctionValidateItem($concurso, $body)
    {
        $errores = [];

        $oferta = $body->Items[$body->Index];
        $subasta = $concurso->getSubastaOutput();
        $producto = $concurso->productos->get($body->Index);

        // Obtenemos los valores de partida.
        $cantidad_min = $producto->oferta_minima;
        $cantidad_max = $producto->cantidad;
        $cotizacion_min = $concurso->precio_minimo;
        $cotizacion_max = $concurso->precio_maximo;
        $unidad_minima = $concurso->unidad_minima;
        $cotizacion_anterior = null;
        $moneda = $concurso->tipo_moneda->nombre;
        $unidad = $producto->unidad_medida->name;
        $descendente = $concurso->tipo_valor_ofertar == 'descendente' ? true : false;
        $mejor_oferta = null;

        // Obtenemos los valores ingresados.
        $cotizacion = $oferta->valores->cotizacion;
        $cantidad = $oferta->valores->cantidad;

        // Verificamos que existan datos.
        if (!$cotizacion || !$cantidad) {
            $errores[] = 'Los campos de Importe y Cantidad son obligatorios.';
        }

        foreach ($subasta['Items'] as $item) {
            // Obtengo la mejor oferta para el producto.
            if ($item['id'] == $producto->id) {
                $mejor_oferta = $item['valores_mejor']['cotizacion'];

                // Obtengo la oferta anterior del oferente para el producto.
                if ($item['id_oferente'] == user()->id) {
                    $cotizacion_anterior = $item['valores']['cotizacion'];
                }
            }
        }

        // Obtengo la cotización de partida para el producto.
        $cotizacion_limite =
            $mejor_oferta && $concurso->solo_ofertas_mejores ?
            $mejor_oferta :
            ($cotizacion_anterior ? $cotizacion_anterior : null);

        $cotizacion_limite =
            $cotizacion_limite && $unidad_minima ?
            (
                $descendente ?
                $cotizacion_limite - $unidad_minima :
                $cotizacion_limite + $unidad_minima
            ) :
            $cotizacion_limite;

        if ($cotizacion_limite) {
            // Reviso si estamos dentro del rango de mejor oferta. Todos tienen la posibilidad de ofertar igual en ese rango.
            if ($descendente && ($cotizacion - $cotizacion_min) <= $unidad_minima) {
                $cotizacion_limite = $mejor_oferta ? $mejor_oferta : $cotizacion_min;
            }

            if ((!$descendente && ($cotizacion_max - $cotizacion) <= $unidad_minima)) {
                $cotizacion_limite = $mejor_oferta ? $mejor_oferta : $cotizacion_max;
            }

            // Verifico que la cotización ingresada sea aceptable.
            if (($descendente && $cotizacion > $cotizacion_limite)) {
                $errores[] = "Cotización mayor a $cotizacion_limite ($moneda) no permitida.";
            } elseif ((!$descendente && $cotizacion < $cotizacion_limite)) {
                $errores[] = "Cotización menor a $cotizacion_limite ($moneda) no permitida.";
            }
        }

        // Verifico que la cotización ingresada esté dentro del rango
        if (
            $cotizacion_min && $cotizacion_min > 0 &&
            $cotizacion < $cotizacion_min
        ) {

            $errores[] = "Cotización no puede ser menor a $cotizacion_min.";

        } elseif (
            $cotizacion_max && $cotizacion_max > 0 &&
            $cotizacion > $cotizacion_max
        ) {

            $errores[] = "Cotización no puede ser mayor a $cotizacion_max.";
        }

        // Validamos la cantidad.
        if ($cantidad > $cantidad_max) {
            $errores[] = "La cantidad máxima es de $cantidad_max ($unidad)";
        } elseif ($cantidad < $cantidad_min) {
            $errores[] = "La cantidad mínima es de $cantidad_min ($unidad)";
        }

        return $errores;
    }

    private function updateDocuments($concurso, $oferente, $body, $economic_proposal, $strict = true)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $absolute_path = filePath($oferente->file_path, true);

            $documents = collect();
            $documents = $documents->merge(isset($body->EconomicProposal->documents) ? $body->EconomicProposal->documents : []);

            // Validación SOLO si $strict === true
            if ($strict) {
                $validator = $this->validateDocuments($body, $concurso, [
                    'economic_documents' => $documents->map(function ($item) {
                        return (array) $item;
                    })->toArray()
                ]);

                if ($validator->fails()) {
                    return [
                        'success' => false,
                        'message' => $validator->errors()->first()
                    ];
                }
            }

            foreach ($documents as $document) {
                $action = isset($document->action) ? $document->action : null;

                switch ($action) {
                    case 'upload':
                        // Si había un archivo previo, lo eliminamos.
                        if (!empty($document->id)) {
                            $to_delete = ProposalDocument::find($document->id);
                            @unlink($absolute_path . DIRECTORY_SEPARATOR . $to_delete->filename);
                            $to_delete->delete();
                        }

                        // Guardamos el nuevo archivo
                        $new_document = new ProposalDocument([
                            'proposal_id' => $economic_proposal->id,
                            'type_id' => (int) (isset($document->type_id) ? $document->type_id : 0),
                            'filename' => isset($document->filename) ? $document->filename : ''
                        ]);
                        $new_document->save();
                        break;

                    case 'clear':
                    case 'delete':
                        // Si el archivo ya estaba guardado
                        if (!empty($document->id)) {
                            $to_delete = ProposalDocument::find($document->id);
                            @unlink($absolute_path . DIRECTORY_SEPARATOR . $to_delete->filename);
                            $to_delete->delete();
                        }
                        break;

                    default:
                        // noop
                        break;
                }
            }

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return [
            'success' => $success,
            'message' => $message
        ];
    }


    private function validate($body, $concurso, $fields)
    {
        $conditional_rules = [];
        $common_rules = [
            'comment' => [
                'string',
                'max:5000',
                'nullable'
            ],
            'payment_deadline' => [
                'required'
            ],
            'values' => [
                'required'
            ],
            'values.*' => [
                'required'
            ],
            'values.*.cotizacion' => [
                'required',
                'numeric',
                'min:0.0001'
            ],
            'values.*.cantidad' => [
                'required',
                'numeric',
                'min:0.0001'
            ]
        ];

        if ($concurso->condicion_pago == 'si') {
            $conditional_rules = array_merge($conditional_rules, [
                'payment_condition' => [
                    'required'
                ]
            ]);
        }

        if ($body->IsSobrecerrado) {
            $conditional_rules = array_merge($conditional_rules, [
                'values.*.fecha' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:365'
                ]
            ]);
        }

        $conditional_rules = array_merge($conditional_rules, [
            'values.*' => [
                function ($attribute, $value, $fail) use ($body, $concurso) {
                    $product = $concurso->productos->where('id', $value['producto'])->first();
                    if ($body->IsGo) {
                        if ((int) $value['cantidad'] <> (int) $product->cantidad) {
                            $fail('La Cantidad Cotizada debe ser igual a ' . $product->cantidad . '.');
                        }
                    } else {
                        if ((int) $product->oferta_minima == (int) $product->cantidad) {
                            if ((int) $value['cantidad'] <> (int) $product->cantidad) {
                                $fail('La Cantidad Cotizada de "' . $product->nombre . '" debe ser igual a ' . $product->cantidad . '.');
                            }
                        } else {
                            if ((int) $value['cantidad'] < (int) $product->oferta_minima || $value['cantidad'] > (int) $product->cantidad) {
                                $fail('La Cantidad Cotizada debe ser mayor a "' . $product->nombre . '" y menor que ' . $product->cantidad . '.');
                            } else {
                                // Validación extra si está dentro del rango
                                if (Carbon::now()->gt(Carbon::parse($concurso->fecha_limite_economicas))) {
                                    $fail('La Fecha de envio de Propuesta ya ha terminado.');
                                }
                            }
                        }
                    }
                }
            ]
        ]);

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules),
            $messages = [
                'payment_deadline.required' => 'Debe seleccionar un plazo de pago',
                'payment_condition.required' => 'Debe seleccionar una condición de pago',
                'values.required' => 'Debe cotizar por lo menos un item.'
            ]
        );
    }

    private function validateDocuments($body, $concurso, $fields)
    {
        $conditional_rules = [];
        $common_rules = [
            'economic_documents.0.filename' => 'required'
        ];

        if ($concurso->estructura_costos === 'si') {
            $conditional_rules = array_merge($conditional_rules, [
                'economic_documents.1.filename' => 'required'
            ]);
        }

        if ($concurso->apu === 'si') {
            $conditional_rules = array_merge($conditional_rules, [
                'economic_documents.2.filename' => 'required'
            ]);
        }

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules),
            $messages = [
                'economic_documents.0.filename.required' => 'Debe cargar una Propuesta Económica.',
                'economic_documents.1.filename.required' => 'Debe cargar la Planilla de costos.',
                'economic_documents.2.filename.required' => 'Debe cargar Análisis de Precios Unitarios.'
            ]
        );
    }
}