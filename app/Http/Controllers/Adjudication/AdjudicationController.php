<?php

namespace App\Http\Controllers\Adjudication;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Services\EmailService;
use App\Models\Concurso;
use App\Models\Step;
use App\Models\Participante;
use App\Models\Producto;
use App\Models\ProposalStatus;

class AdjudicationController extends BaseController
{
    public function acceptOrDecline(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', user()->offerer_company_id)->first();

            switch ($body->Action) {
                case 'accept':
                    $template = rootPath(config('app.templates_path')) . '/email/client-adjudication-accepted.tpl';
                    $title = 'Adjudicación aceptada';
                    $message = 'Adjudicación aceptada con éxito.';
                    $accept = true;
                    $etapa_actual = Participante::ETAPAS['adjudicacion-aceptada'];
                    break;
                case 'decline':
                    $template = rootPath(config('app.templates_path')) . '/email/client-adjudication-rejected.tpl';
                    $title = 'Adjudicación rechazada';
                    $message = 'Adjudicación rechazada con éxito.';
                    $accept = false;
                    $etapa_actual = Participante::ETAPAS['adjudicacion-rechazada'];

                    $concurso->update([
                        'adjudicado' => 0
                    ]);

                    $oferentes = $concurso->oferentes
                        ->where('id_offerer', '<>', user()->offerer_company_id)
                        ->where('etapa_actual', Participante::ETAPAS['economica-presentada'])
                        ->all();

                    foreach ($oferentes as $key => $value) {
                        $value->update([
                            'rechazado' => 0
                        ]);
                    }
                    break;
            }

            $oferente->update([
                'acepta_adjudicacion' => $accept,
                'acepta_adjudicacion_fecha' => Carbon::now()->format('Y-m-d'),
                'etapa_actual' => $etapa_actual
            ]);

            $connection->commit();

            // Si se aceptó la adjudicación, actualizar solpeds
            if ($accept) {
                $this->updateSolpedsWhenAdjudicationAccepted($concurso);
                // Enviar correo a solicitantes de SOLPEDs involucradas
                try {
                    $this->sendEmailAdjudicationAcceptedToSolpeds($concurso, $oferente);
                } catch (\Exception $e) {
                    error_log('Error enviando emails de adjudicación aceptada a solicitantes de SOLPED: ' . $e->getMessage());
                }
            }

            $subject = $concurso->nombre . ' - ' . $title;
            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'oferente' => $oferente,
            ]);
            
            $result = $emailService->send(
                $html,
                $subject,
                [$concurso->cliente->email],
                $concurso->cliente->full_name
            );
            //if (!$result['success']) {
            //    $error = true;
            //}
            $success = true;

        } catch (\Exception $e) {
            $connection->rollback();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => route('concursos.oferente.serveDetail', [
                    'id' => $concurso->id,
                    'type' => Concurso::TYPES[$concurso->tipo_concurso],
                    'step' => Step::STEPS['offerer']['adjudicado']
                ])
            ]
        ], $status);
    }

    public function send(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $redirect_url = null;
        $error = false;
        // 1) PONER ESTO CERCA DEL INICIO DE send(), junto a otras inits:
        $adjudicationResult = [];       
        $cot_tot = 0;                   

        try {
            $body = json_decode($request->getParsedBody()['Data']);
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $type = $body->Type;

            $concurso = Concurso::find($body->IdConcurso);
            $rondaActual = $concurso->ronda_actual;
            $adjudicatedOffererIds = [];
            $losers = collect();
            $offersByOfferer = collect();

            $common_fields = [
                'comment' => $body->Comment
            ];

            $subject = $concurso->nombre;


            if ($type == 'integral') {
                $adjudicacion = 1;
                $oferenteId = (int) $body->Data;

                $validation = $this->validate($common_fields, $type);
                if ($validation->fails()) {
                    $message = $validation->errors()->first();
                    $status = 422;
                    $error = true;
                } else {
                    $oferente = $this->getOfferers($type, $concurso, $oferenteId);

                    $proposal = $oferente->economic_proposal->where('participante_id', $oferente->id)->where('numero_ronda', $rondaActual)->where('type_id', 2)->first();
                    

                    $productsOfferer = $concurso->productos->map(
                        function ($product) use ($proposal) {
                            $oferta = array_values(
                                array_filter(
                                    $proposal->values,
                                    function ($item) use ($product) {
                                        return $item['producto'] == $product->id;
                                    }
                                )
                            )[0];
                            $oferta['cotizacion'] = $oferta['total'];
                            return $oferta;
                        }
                    );

                    $adjudicationResult = [];
                    $adjudicated_products = collect();
                    $cot_tot = 0; // Variable para acumular el total de cotización
                    foreach ($productsOfferer as $oferta) {
                        $producto = Producto::find((int) $oferta['producto']);
                        array_push($adjudicationResult, [
                            'itemId' => $producto->id,
                            'itemSolicitado' => $producto->cantidad,
                            'itemNombre' => $producto->nombre,
                            'oferenteId' => $oferente->id_offerer,
                            'cantidad' => $oferta['cantidad'],
                            'cotUnitaria' => $oferta['cotUnitaria'],
                            'cotizacion' => $oferta['cotizacion'],
                            'cantidadCot' => $oferta['cantidadCot'],
                            'cantidadAdj' => $oferta['cantidadCot'],
                            'total' => $oferta['total'],
                            'moneda' => $concurso->tipo_moneda->nombre,
                            'unidad' => $producto->unidad_medida->name,
                            'fecha' => $oferta['fecha'],
                        ]);
                        $cot_tot += $oferta['total']; // Acumulamos el valor de "total"
                    }
                    $adjudicated_products = collect($adjudicationResult);
                    $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['accepted'])->first();
                    $proposal->update([
                        'status_id' => $proposal_status->id
                    ]);
                    
                    
                    $oferente->update([
                        'etapa_actual' => Participante::ETAPAS['adjudicacion-pendiente'],
                        'adjudicacion' => $adjudicacion
                    ]);
                    array_push($adjudicatedOffererIds, $oferente->id);
                }
            }

            if ($type == 'individual') {
                $adjudicacion = 2;
                
                $stringified_items = explode(',', $body->Data);
                $jsonArray = array();
                foreach ($stringified_items as $item) {
                    $parsed_offer = explode(':', $item);
                    $jsonArray[] = array("offerer_id" => (int) $parsed_offer[0], "product_id" => (int) $parsed_offer[1]);
                }


                $validation = $this->validate($common_fields, $type);

                if ($validation->fails()) {
                    $message = $validation->errors()->first();
                    $status = 422;
                    $error = true;
                } else {
                    $adjudicated_products = collect();
                    $adjudicationResult = [];
                    $adjudicatedOffererIds = [];
                    foreach ($jsonArray as $item) {
                        array_push($adjudicatedOffererIds, $item['offerer_id']);
                        $oferente = $concurso->oferentes->where('id_offerer', $item['offerer_id'])->first();
                        $proposal = $oferente->economic_proposal->where('participante_id', $oferente->id)->where('numero_ronda', $rondaActual)->where('type_id', 2)->first();
                        $products = $concurso->productos->where('id', $item['product_id']);
                        $productsOfferer = $products->map(
                            function ($product) use ($proposal) {
                                $oferta = array_values(
                                    array_filter(
                                        $proposal->values,
                                        function ($item) use ($product) {
                                            return $item['producto'] == $product->id;
                                        }
                                    )
                                )[0];
                                $oferta['cotizacion'] = $oferta['total'];
                                return $oferta;
                            }
                        );
                        foreach ($productsOfferer as $oferta) {
                            $producto = Producto::find((int) $oferta['producto']);
                            $producto->id_offerer = $oferente->id_offerer;
                            $cot_tot = 0; // Variable para acumular el total de cotización
                            array_push($adjudicationResult, [
                                'itemId' => $producto->id,
                                'itemSolicitado' => $producto->cantidad,
                                'itemNombre' => $producto->nombre,
                                'oferenteId' => $oferente->id_offerer,
                                'cantidad' => $oferta['cantidad'],
                                'cotUnitaria' => $oferta['cotUnitaria'],
                                'cotizacion' => $oferta['cotizacion'],
                                'cantidadCot' => $oferta['cantidadCot'],
                                'cantidadAdj' => $oferta['cantidadCot'],
                                'total' => $oferta['total'],
                                'moneda' => $concurso->tipo_moneda->nombre,
                                'unidad' => $producto->unidad_medida->name,
                                'fecha' => $oferta['fecha'],
                            ]);
                            $cot_tot += $oferta['total']; // Acumulamos el valor de "total"
                        }
                        $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['accepted'])->first();
                        $proposal->update([
                            'status_id' => $proposal_status->id
                        ]);

                        $oferente->update([
                            'etapa_actual' => Participante::ETAPAS['adjudicacion-pendiente'],
                            'adjudicacion' => $adjudicacion
                        ]);
                    }
                    $adjudicated_products = collect($adjudicationResult);
                    $oferentes = $this->getOfferers($type, $concurso, $adjudicatedOffererIds);
                }
            }

            // 2) REEMPLAZAR COMPLETO EL IF ($type == 'manual') POR ESTO:
            if ($type == 'manual') {
                $adjudicacion = 3;
                $manual_adjudication = $body->Data;

                // Tomamos los ítems crudos desde el body (para validar cantidades > 0)
                $itemsTemp = json_decode($request->getParsedBody()['Data'], true)['Data']['items'];

                // Filtramos sólo los que tengan quantity > 0
                $items = array_filter($itemsTemp, function ($v) {
                    return isset($v['quantity']) && $v['quantity'] > 0;
                });

                $fields = array_merge($common_fields, [
                    'items' => $items
                ]);

                $validation = $this->validate($fields, $type);

                if ($validation->fails()) {
                    $message = $validation->errors()->first();
                    $status  = 422;
                    $error   = true;
                } else {
                    // Colecciones útiles
                    $itemsAdjudicated = collect($items);

                    // IDs de PARTICIPANTE que vienen en los items adjudicados
                    $participanteIds = $itemsAdjudicated->pluck('offerer_id')->filter()->unique()->values();

                    // Los oferentes (MODELO Participante) adjudicados salen DIRECTO del concurso
                    // Evitamos depender de getOfferers() para no confundir id vs id_offerer
                    $oferentes = $concurso->oferentes->whereIn('id', $participanteIds->all());

                    // Para perdedores: necesitamos los id_offerer de los ganadores
                    $adjudicatedOffererIds = $oferentes->pluck('id_offerer')->unique()->values()->all();

                    // Construimos resultados de adjudicación por oferente/ítem
                    foreach ($oferentes as $oferente) {
                        // Propuesta económica del oferente (participante) en la ronda actual
                        $proposal = $oferente->economic_proposal
                            ->where('participante_id', $oferente->id)
                            ->where('numero_ronda', $rondaActual)
                            ->where('type_id', 2)
                            ->first();

                        // Productos adjudicados a ESTE participante
                        $productsAdjIds = $itemsAdjudicated
                            ->where('offerer_id', $oferente->id) // <- acá comparamos contra id de PARTICIPANTE
                            ->pluck('product_id')
                            ->unique()
                            ->values()
                            ->all();

                        $products = $concurso->productos->whereIn('id', $productsAdjIds);

                        // Mapear valores ofertados para cada producto del participante
                        $productsOfferer = $products->map(function ($product) use ($proposal) {
                            // Buscamos el renglón en proposal->values que coincide con el producto
                            $oferta = collect($proposal->values)->first(function ($item) use ($product) {
                                return (int)$item['producto'] === (int)$product->id;
                            });

                            // Si por alguna razón no lo encontramos, evita notice/offset
                            if (!$oferta) {
                                return null;
                            }

                            // Normalizamos "cotizacion" = "total"
                            $oferta['cotizacion'] = $oferta['total'];
                            return $oferta;
                        })->filter(); // fuera nulls defensivos

                        foreach ($productsOfferer as $oferta) {
                            $producto = Producto::find((int) $oferta['producto']);

                            // Cantidad adjudicada tomada del item adjudicado concreto
                            $prodAdj = $itemsAdjudicated
                                ->where('product_id', $producto->id)
                                ->where('offerer_id', $oferente->id) // id de PARTICIPANTE
                                ->first();

                            $cantidadAdj = $prodAdj ? (int)$prodAdj['quantity'] : 0;

                            $adjudicationResult[] = [
                                'itemId'         => $producto->id,
                                'itemSolicitado' => $producto->cantidad,
                                'itemNombre'     => $producto->nombre,
                                'oferenteId'     => $oferente->id_offerer,                 // <- id_offerer para mails/filtros
                                'cantidad'       => $oferta['cantidad'],
                                'cotUnitaria'    => $oferta['cotUnitaria'],
                                'cotizacion'     => $oferta['cotizacion'],
                                'cantidadCot'    => $oferta['cantidadCot'],
                                'cantidadAdj'    => $cantidadAdj,                          // <- manual
                                'total'          => $oferta['total'],
                                'moneda'         => $concurso->tipo_moneda->nombre,
                                'unidad'         => $producto->unidad_medida->name,
                                'fecha'          => $oferta['fecha'],
                            ];

                            // Vamos acumulando el total
                            $cot_tot += (float)$oferta['total'];
                        }

                        // Marcar propuesta y participante
                        $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['accepted'])->first();
                        if ($proposal) {
                            $proposal->update(['status_id' => $proposal_status->id]);
                        }

                        $oferente->update([
                            'etapa_actual' => Participante::ETAPAS['adjudicacion-pendiente'],
                            'adjudicacion' => $adjudicacion
                        ]);
                    }

                    // Dejamos estas colecciones listas para la fase de envío y para perdedores:
                    $adjudicated_products = collect($adjudicationResult);
                    // IMPORTANTE: también dejamos $oferentes definido para el envío de correos a ganadores
                    // (ya lo tenemos arriba)
                }
            }

                $listEconomicas = ['ConcursoEconomicas' => []];

            if (!$error) {
                $concurso->update([
                    'adjudicacion_items' => json_encode($adjudicationResult),
                    'total_cotizacion' => $cot_tot, // Guardamos el total de cotización
                    'adjudicacion_comentario' => $body->Comment && !empty($body->Comment) ? $body->Comment : null,
                    'adjudicado' => true
                ]);
                require rootPath() . '/app/OldServices/calculos-etapas.php';
                calcularEtapaAnalisisOfertas($listEconomicas, $concurso->id);
            }

            if (!$error) {
                // Ganadores
                if ($type == 'integral') {
                    $result = $this->sendEmailAdjudication($type, $subject, $concurso, $oferente, $adjudicated_products);
                } else {
                    // individual y manual
                    foreach ($oferentes as $oferente) {
                        $adjudicated_products_offerer = $adjudicated_products->where('oferenteId', $oferente->id_offerer);
                        $result = $this->sendEmailAdjudication($type, $subject, $concurso, $oferente, $adjudicated_products_offerer);
                        if (!$result['success']) {
                            break;
                        }
                    }
                }
            }
            
            if (!$error && $result['success']) {
                if($concurso->is_sobrecerrado){
                    $losers = $concurso->oferentes
                    ->where('is_economica_revisada', true)
                    ->whereNotIn('id_offerer', $adjudicatedOffererIds);
                }else{
                    $losers = $concurso->oferentes
                    ->where('is_economica_presentada', true)
                    ->whereNotIn('id_offerer', $adjudicatedOffererIds);
                }
                
                if (count($losers) > 0) {
                    foreach ($losers as $oferente) {
                        if ($oferente->has_economica_presentada) {
                            $proposal = $oferente->economic_proposal;
                            $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['rejected'])->first();
                            $proposal->update([
                                'status_id' => $proposal_status->id
                            ]);

                            $oferente->update([
                                'rechazado' => true,
                                'adjudicacion' => $adjudicacion
                            ]);
                        } else {
                            if ($oferente->has_tecnica_presentada) {
                                $proposal = $oferente->technical_proposal;
                                $proposal_status = ProposalStatus::where('code', ProposalStatus::CODES['rejected'])->first();
                                $proposal->update([
                                    'status_id' => $proposal_status->id
                                ]);
                            }

                            $oferente->update([
                                'rechazado' => true
                            ]);
                        }
                    }
                    $result = $this->sendEmailNotAdjudication($type, $subject, $concurso, $losers);
                }
            }

            if (!$error && $result['success']) {
                // Resultado del concurso
                if ($concurso->is_sobrecerrado && $concurso->aperturasobre == 'si') {
                    $result = $this->sendEmailResultContest($concurso, $listEconomicas);
                }
            }

            if (!$error && $result['success']) {
                $connection->commit();
                $success = true;
                $message = 'La adjudicación se realizó con éxito.';
                $redirect_url = route('concursos.cliente.serveList');
            } else {
                $connection->rollBack();
                $success = false;
                $message = $message ?? 'Han ocurrido errores al enviar los correos.';
            }

        } catch (\Exception $e) {
            $connection->rollback();
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

    private function sendEmailAdjudication($type, $subject, $concurso, $oferente, $adjudicated_products)
    {
        $emailService = new EmailService();
        $title = $subject . '-' . 'Resultado Calificación Económica ' . $type;
        $template = rootPath(config('app.templates_path')) . '/email/adjudication-accepted.tpl';
        //Email de adjudication asignada
        $html = $this->fetch($template, [
            'title' => $title,
            'ano' => Carbon::now()->format('Y'),
            'concurso' => $concurso,
            'company_name' => $oferente->company->business_name,
            'adjudicated_products' => $adjudicated_products
        ]);

        $result = $emailService->send(
            $html,
            $subject,
            $oferente->company->users->pluck('email'),
            $oferente->company->business_name
        );

        return $result;
    }

    private function sendEmailResultContest($concurso, $listEconomicas)
    {
        $emailService = new EmailService();
        $oferentes = $concurso->oferentes->where('has_economica_presentada', true);
        $templateComparisonPrices = rootPath(config('app.templates_path')) . '/email/adjudication-comparison-prices.tpl';
        foreach ($oferentes as $oferente) {
            $html = $this->fetch($templateComparisonPrices, [
                'title' => 'Comparativa de precios',
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'company_name' => $oferente->company->business_name,
                'listEconomicas' => $listEconomicas
            ]);

            $result = $emailService->send(
                $html,
                $subject = $concurso->nombre . ' - Comparativa de precios',
                $oferente->company->users->pluck('email'),
                $oferente->company->business_name
            );
            if (!$result['success']) {
                break;
            }
        }
        return $result;
    }

    private function sendEmailNotAdjudication($type, $subject, $concurso, $oferentesLoser)
    {
        $emailService = new EmailService();
        $template = rootPath(config('app.templates_path')) . '/email/adjudication-rejected.tpl';
        $title = $subject . '-' . 'Resultado Calificación Económica ' . $type;
        foreach ($oferentesLoser as $oferente) {
            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'company_name' => $oferente->company->business_name
            ]);
            $result = $emailService->send(
                $html,
                $subject,
                $oferente->company->users->pluck('email'),
                $oferente->company->business_name
            );
            if (!$result['success']) {
                return $result;
            }

        }
        return $result;

    }

    public function getProduct(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];

        try {

            $product = Producto::find((int) $params['id']);
            $concurso = $product->concurso;
            $rondaActual = $concurso->ronda_actual;

            $offerers = [];

            foreach ($concurso->oferentes->where('has_economica_presentada', true) as $offerer) {
                $proposal = $offerer->economic_proposal->where('participante_id', $offerer->id)->where('numero_ronda', $rondaActual)->where('type_id', 2)->first();
                
                
                $oferta = array_values(
                    array_filter(
                        $proposal->values,
                        function ($item) use ($product) {
                            return $item['producto'] == $product->id;
                        }
                    )
                )[0];

                if ($oferta) {
                    $offerers[] = [
                        'id' => (string) $offerer->id,
                        'text' => $offerer->company->business_name
                    ];
                }
            }

            $result = [
                'quantity' => $product->cantidad,
                'offerers' => $offerers
            ];

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
                'result' => $result
            ]
        ], $status);
    }

    public function getOffererByProduct(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];

        try {

            $product = Producto::find((int) $params['id']);
            $concurso = $product->concurso;
            $rondaActual = $concurso->ronda_actual;
            
            $offerer = Participante::find((int) $params['offerer_id']);

            $proposal = $offerer->economic_proposal->where('participante_id', $offerer->id)->where('numero_ronda', $rondaActual)->where('type_id', 2)->first();
            $oferta = array_values(
                array_filter(
                    $proposal->values,
                    function ($item) use ($product) {
                        return $item['producto'] == $product->id;
                    }
                )
            )[0];

            if ($oferta) {
                $result = [
                    'price' => $oferta['cotizacion'],
                    'quantity' => $oferta['cantidad']
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
                'result' => $result
            ]
        ], $status);
    }

    public function check(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $body = json_decode($request->getParsedBody()['Data'], true);

            $validation = $this->validate([
                'items' => $body
            ], 'manual');

            if ($validation->fails()) {
                $success = false;
                $message = $validation->errors()->first();
            } else {
                $success = true;
            }

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }

    private function validate($fields, $type)
    {
        $conditional_rules = [];
        $common_rules = [
            'comment' => [
                'string',
                'max:1000',
                'nullable'
            ]
        ];

        if ($type === 'manual') {
            $conditional_rules = array_merge($conditional_rules, [
                'items' => [
                    'required'
                ],
                'items.*' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $product_quantity = (int) $value['product']['quantity'];
                        $offerer_quantity = (int) $value['offerer']['quantity'];
                        $offerer_total_quantity = (int) $value['offererTotalQuantity'];
                        $total_quantity = (int) $value['totalQuantity'];
                        $product = Producto::find((int) $value['product_id']);
                        if ($offerer_total_quantity > $offerer_quantity) {
                            $offerer = Participante::find((int) $value['offerer_id']);
                            $fail('La suma de las cantidades asignadas al Oferente "' . $offerer->user->offerer_company->business_name . '" para el Item "' . $product->nombre . '" no puede ser mayor a ' . $offerer_quantity . '.');
                        }
                        if ($total_quantity > $product_quantity) {
                            $fail('La suma de las cantidades asignadas para el Item "' . $product->nombre . '" no puede ser mayor a ' . $product_quantity . '.');
                        }
                    },
                ],
                'items.*.quantity' => [
                    'required',
                    'numeric',
                    'gt:0'
                ],
                'items.*.product_id' => [
                    'required',
                    'exists:hijos_x_concursos,id'
                ],
                'items.*.offerer_id' => [
                    'required',
                    'exists:concursos_x_oferentes,id'
                ]
            ]);
        }

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules),
            $messages = [
                'items.required' => 'Debe ingresar valores para adjudicar.'
            ],
            $customAttributes = [
                'items.*.quantity' => 'Cantidad Asignada',
                'items.*.product_id' => 'Item',
                'items.*.offerer_id' => 'Oferente'
            ]
        );
    }

    private function getOfferers($type, $concurso, $ids)
    {
        if ($type == 'integral') {
            return $concurso->oferentes->where('id_offerer', $ids)->first();
        } else {
            return $concurso->oferentes->whereIn('id_offerer', $ids);
        }
    }

    /**
     * Actualizar solpeds a estado 'adjudicada' cuando hay oferentes con etapa 'adjudicacion-aceptada'
     * 
     * @param \App\Models\Concurso $concurso
     */
    private function updateSolpedsWhenAdjudicationAccepted($concurso)
    {
        try {
            error_log("=== ACTUALIZANDO SOLPEDS POR ADJUDICACION ===");
            error_log("Concurso ID: " . $concurso->id);
            error_log("created_from_solped: " . $concurso->created_from_solped);
            
            // 1. Verificar si el concurso tiene solpeds asociadas
            if (empty($concurso->created_from_solped)) {
                error_log("No hay solpeds asociadas");
                return;
            }

            // 2. Obtener IDs de solpeds (separados por comas)
            $solpedIds = array_map('intval', array_map('trim', explode(',', $concurso->created_from_solped)));
            error_log("Solped IDs: " . json_encode($solpedIds));

            // 3. Verificar si hay oferentes con 'adjudicacion-aceptada'
            $hasAdjudicationAccepted = \App\Models\Participante::where('id_concurso', $concurso->id)
                ->where('etapa_actual', \App\Models\Participante::ETAPAS['adjudicacion-aceptada'])
                ->exists();

            error_log("¿Hay adjudicacion-aceptada?: " . ($hasAdjudicationAccepted ? 'Sí' : 'No'));

            if ($hasAdjudicationAccepted) {
                // 4. Actualizar solpeds a estado 'adjudicada'
                $updated = \App\Models\Solped::whereIn('id', $solpedIds)
                    ->update([
                        'estado_actual' => 'adjudicada',
                        'etapa_actual' => 'finalizada',
                        'updated_at' => \Carbon\Carbon::now()
                    ]);

                error_log("Solpeds actualizadas: " . $updated);
                error_log("=== FIN ACTUALIZACIÓN SOLPEDS ===");
            }

        } catch (\Exception $e) {
            error_log("Error en updateSolpedsWhenAdjudicationAccepted: " . $e->getMessage());
        }
    }

    /**
     * Enviar email a solicitante/s de las SOLPEDs involucradas cuando se acepta la adjudicación
     * Incluye proveedor adjudicado y fecha/hora de la aceptación
     *
     * @param \App\Models\Concurso $concurso
     * @param \App\Models\Participante $oferente
     */
    private function sendEmailAdjudicationAcceptedToSolpeds($concurso, $oferente)
    {
        if (empty($concurso->created_from_solped)) {
            return;
        }

        $emailService = new EmailService();
        $template = rootPath(config('app.templates_path')) . '/email/solped-adjudicacion-aceptada.tpl';

        $solpedIds = array_map('intval', array_map('trim', explode(',', $concurso->created_from_solped)));
        $solpeds = \App\Models\Solped::whereIn('id', $solpedIds)->get();

        $adjudicado = $oferente && $oferente->company ? $oferente->company->business_name : 'Proveedor adjudicado';
        $fechaHora = Carbon::now()->format('d/m/Y H:i');

        foreach ($solpeds as $solped) {
            try {
                if (!$solped->solicitante || empty($solped->solicitante->email)) {
                    continue;
                }

                $subject = 'Solicitud #' . $solped->id . ' - Adjudicación aceptada';

                $html = $this->fetch($template, [
                    'title' => $subject,
                    'ano' => Carbon::now()->format('Y'),
                    'concurso' => $concurso,
                    'user' => $solped->solicitante,
                    'solped' => $solped,
                    'adjudicado' => $adjudicado,
                    'fecha_hora' => $fechaHora,
                ]);

                $emailService->send(
                    $html,
                    $subject,
                    [$solped->solicitante->email],
                    $solped->solicitante->full_name
                );
            } catch (\Exception $e) {
                error_log("Error enviando email de adjudicación aceptada para SOLPED {$solped->id}: " . $e->getMessage());
            }
        }
    }
}