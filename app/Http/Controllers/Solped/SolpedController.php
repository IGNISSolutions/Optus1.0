<?php

namespace App\Http\Controllers\Solped;
use App\Http\Controllers\BaseController;
use App\Models\InvitationStatus;
use App\Models\ProposalStatus;
use App\Models\Solped;
use App\Models\SolpedDocument;
use App\Models\UserType;
use Carbon\Traits\ToStringFormat;
use PhpMyAdmin\Console;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\Pais;
use App\Models\Category;
use App\Models\Measurement;
use App\Models\Catalogo;
use App\Models\Alcance;
use App\Models\Concurso;
use App\Models\Participante;
use App\Models\Producto;
use App\Models\Provincia;
use App\Models\TipoOperacion;
use App\Models\Sheet;
use App\Models\SheetType;
use App\Models\ConvocatoriaTipo;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\PlantillaTecnica;
use App\Models\PlantillaTecnicaTipo;
use App\Models\PolicyAmount;
use App\Models\Step;
use App\Services\EmailService;
use \Exception as Exception;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\OffererCompany;
use App\Models\Evaluacion;
use DateTimeZone;
use DateTime;
use stdClass;


class SolpedController extends BaseController
{


    public function serveTypeList(Request $request, Response $response, $params)
    {
        return $this->render($response, 'solped/solicitante/list/monitor-solped.tpl', [
            'page' => 'solped',
            'accion' => 'monitor-solped',
            'tipo' => 'solicitante',
            'title' => 'Monitor de Solicitudes de Pedido',
            'pre_scripts_child' => '',
            'post_scripts_child' => ''
        ]);
    }

    

    public function serveDetail(Request $request, Response $response, $params)
    {
        try {
            $id = (int) $params['id'];
            
            if (isAdmin()) {
                $solped = Solped::find($id);
            } else {
                $user = user();
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($id);
            }

            abort_if($request, $response, !$solped, true, 404);

            return $this->render($response, 'solped/solicitante/detail.tpl', [
                'page' => 'solped',
                'accion' => 'poretapassolicitante', 
                'etapa' => $params['etapa'],
                'idSolped' => $params['id'],
                'title' => 'Solped Titulo'
            ]);
            
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, Response $response, $params) {

        date_default_timezone_set(user()->customer_company->timeZone);
        $success = false;
        $message = null;
        $status = 200;
        $list = [];

        // Archivo de log
        $logFile = __DIR__ . '/debug_detail.txt';
        $fp = fopen($logFile, 'a');
        fwrite($fp, "\n=== detail INICIO === " . date('d-m-Y H:i:s') . " ===\n");
        fwrite($fp, "Params recibidos: " . print_r($params, true) . "\n");

        try {
            $user = user();
            fwrite($fp, "Usuario: ID={$user->id}, type_id={$user->type_id}, company={$user->customer_company->id}\n");

            if (isAdmin()) {
                fwrite($fp, "Usuario es ADMIN\n");
                $solped = Solped::find($params['id']);
            } else {
                fwrite($fp, "Usuario NO es ADMIN\n");
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($params['id']);
            }

            if (!$solped) {
                fwrite($fp, "Solped no encontrado\n");
                throw new Exception("Solped no encontrado con id {$params['id']}");
            }

            fwrite($fp, "Solped encontrado: ID={$solped->id}, etapa={$solped->etapa_actual}\n");

            // Common data siempre se carga
            $common_data = [
                'IdSolicitud'     => $solped->id,
                'Nombre'          => $solped->nombre,
                'IdSolicitante'   => $solped->solicitante->id,
                'Solicitante'     => $solped->solicitante->full_name,
                'TipoCompraId'    => $solped->tipo_compra ? (int)$solped->tipo_compra : null,
                'CodigoInterno'   => $solped->codigo_interno ?: null,
                'Descripcion'     => $solped->descripcion,
                'AreaSolicitante' => $solped->area_sol ,
                'CompradorSugerido'=> $solped->comprador_sugerido ? $solped->comprador_sugerido->full_name : null,
                'FechaResolucion'  => $solped->fecha_resolucion ? $solped->fecha_resolucion->format('d-m-Y H:i:s') : null,
                'FechaEntrega'    => $solped->fecha_entrega ? $solped->fecha_entrega->format('d-m-Y H:i:s') : null,
                'FechaCreacion'   => $solped->fecha_alta ? $solped->fecha_alta->format('d-m-Y H:i:s') : null,
                'FechaEnvioComprador' => $solped->fecha_envio_a_comprador ? $solped->fecha_envio_a_comprador->format('d-m-Y H:i:s') : null,
                'Eliminado'       => $solped->deleted_at ? true : false,
                'UsuarioReject'   => $solped->usuario_rechazo ?: null,
                'UsuarioAccept'   => $solped->id_comprador_decision ?: null,
                'FechaRechazo'    => $solped->fecha_rechazo ? $solped->fecha_rechazo->format('d-m-Y H:i:s') : null,
                'FechaAceptacion' => $solped->fecha_aceptacion ? $solped->fecha_aceptacion->format('d-m-Y H:i:s') : null,
                'RejectComment'   => $solped->reject_comment ?: null,
                'ReturnComment'   => $solped->return_comment ?: null,
                'Etapa'         => $solped->etapa_actual,
                'EstadoActual'   => $solped->estado_actual,
                'CompradorDecision' => $solped->comprador_decision ? $solped->comprador_decision->full_name : null,
                'CompradorFirstRevision' => $solped->comprador_first_revision ? $solped->comprador_first_revision->full_name : null,
                'FechaFirstRevision' => $solped->fecha_first_revision ? $solped->fecha_first_revision->format('d-m-Y H:i:s') : null,
                'FechaDevolucion' => $solped->fecha_devolucion ? $solped->fecha_devolucion->format('d-m-Y H:i:s') : null,
                // Comprador que tomó la decisión + fecha (rechazo/aceptación/devolución/adjudicación)
                'CompradorDecisionFecha' => (function() use ($solped) {
                    $comprador = $solped->comprador_decision ? $solped->comprador_decision->full_name : null;
                    $fecha = null;
                    if ($solped->estado_actual === 'rechazada' && $solped->fecha_rechazo) {
                        $fecha = $solped->fecha_rechazo->format('d-m-Y H:i:s');
                    } elseif ($solped->estado_actual === 'aceptada' && $solped->fecha_aceptacion) {
                        $fecha = $solped->fecha_aceptacion->format('d-m-Y H:i:s');
                    } elseif ($solped->estado_actual === 'devuelta' && $solped->fecha_devolucion) {
                        $fecha = $solped->fecha_devolucion->format('d-m-Y H:i:s');
                    } elseif ($solped->estado_actual === 'adjudicada' && $solped->fecha_aceptacion) {
                        // Para estado adjudicada, mostrar la fecha de aceptación de la decisión
                        $fecha = $solped->fecha_aceptacion->format('d-m-Y H:i:s');
                    }
                    return ($comprador && $fecha) ? ($comprador . ' - ' . $fecha) : null;
                })(),
                'CompradorDevolucionFecha' => $solped->comprador_decision && $solped->fecha_devolucion ? 
                    $solped->comprador_decision->full_name . ' - ' . $solped->fecha_devolucion->format('d-m-Y H:i:s') : null,
                'CancelMotive'   => $solped->cancel_motive ?: null,
            ];

            fwrite($fp, "Common data cargado\n");

            // Armado de productos
            $productos = [];
            foreach ($solped->productos as $producto) {
                $prodArray = [
                    'Id'                => $producto->id,
                    'Nombre'            => $producto->nombre,
                    'Descripcion'       => $producto->descripcion,
                    'Cantidad'          => (float)$producto->cantidad,
                    'OfertaMinima'      => (float)$producto->oferta_minima,
                    'UnidadMedidaId'    => $producto->unidad_medida && $producto->unidad_medida->id ? (int)$producto->unidad_medida->id : null,
                    'UnidadMedidaNombre'=> $producto->unidad_medida && $producto->unidad_medida->name ? $producto->unidad_medida->name : '',
                    'TargetCost'        => (float)$producto->targetcost,
                ];
                fwrite($fp, "Producto agregado: " . print_r($prodArray, true) . "\n");
                $productos[] = $prodArray;
            }
            fwrite($fp, "Total productos: " . count($productos) . "\n");

            // FilePath
            $file_path = '/img/solped/';
            $document = SolpedDocument::where('solped_id', $solped->id)->first();
            fwrite($fp, "FilePath base: {$file_path}\n");
            fwrite($fp, "Document encontrado: " . ($document ? $document : 'NO') . "\n");

            // Siempre llenamos $list
            $list = array_merge($common_data, [
                'Productos'        => $productos,
                'FilePath'         => $file_path,
                'FilePathComplete' => $file_path && $document ? $file_path . $document->filename : null,
                'Etapa'            => $solped->etapa_actual,
            ]);

            $success = true;
            fwrite($fp, "SUCCESS: List armado correctamente\n");

            $breadcrums = [
                ['description' => 'Solped', 'url' => route('solped.serveList')],
                ['description' => 'Detalle']
            ];

        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
            fwrite($fp, "ERROR: {$message}\n");
        }

        fwrite($fp, "List final: " . print_r($list, true) . "\n");
        fwrite($fp, "=== detail FIN ===\n\n");
        fclose($fp);



        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => isset($breadcrums) ? $breadcrums : []
            ]
        ], $status);
    }




    public function serveList(Request $request, Response $response, $params)
    {
        $title = "Solicitud de Pedido";
        $description = null;
        
        return $this->render($response, 'solped/solicitante/list.tpl', [
        'page' => 'solped',
        'id' => 0,
        'title' => $title,
        'description' => $description,
        ]);

    }

    public function serveCreate(Request $request, Response $response, $params)
    {
        $title = "Solicitud de Pedido";
        $description = null;

        // Breadcrumbs para el render del template (lado servidor)
        $breadcrumbs = [
            ['description' => 'Solped', 'url' => route('solped.serveList')],
            ['description' => 'Nuevo']
        ];

        return $this->render($response, 'solped/solicitante/principal.tpl', [
            'page' => 'solped',
            'id' => 0,
            'title' => $title,
            'description' => $description,
            'breadcrumbs' => $breadcrumbs,
            'isCopy' => 0

        ]);
    }

    public function serveEdit(Request $request, Response $response, $params)
    {
        $title = "Solicitud de Pedido";
        $description = null;
        $id = $params['id'] ?? 0;

        $breadcrumbs = [
            ['description' => 'Solped', 'url' => route('solped.serveList')],
            ['description' => 'Edición']
        ];

        return $this->render($response, 'solped/solicitante/principal.tpl', [
            'page' => 'solped',
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'breadcrumbs' => $breadcrumbs,
            'isCopy' => 0

        ]);
    }

    public function checkProducts(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status  = 200;

        try {
            // Parseo defensivo del body
            $parsed = $request->getParsedBody();
            $json   = isset($parsed['Data']) ? $parsed['Data'] : null;

            if ($json === null) {
                throw new \Exception('Payload inválido: falta la clave "Data".', 400);
            }

            $body = json_decode($json);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON inválido en "Data": '.json_last_error_msg(), 400);
            }

            $body->Products        = isset($body->Products) && is_array($body->Products) ? $body->Products : [];

            $fields = [ 'products' => [] ];
            foreach ($body->Products as $product) {
                // Casting defensivo
                $name            = isset($product->name) ? (string)$product->name : '';
                $quantity        = isset($product->quantity) ? (float)$product->quantity : 0.0;
                $minimumQuantity = isset($product->minimum_quantity) ? (float)$product->minimum_quantity : 0.0;
                $measurementId   = isset($product->measurement_id) ? (int)$product->measurement_id : 0;

                $fields['products'][] = [
                    'nombre'         => $name,
                    'cantidad'       => $quantity,
                    'oferta_minima'  => $minimumQuantity,
                    'unidad'         => $measurementId,
                ];
            }

            $success = true;
            $message = null;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status  = method_exists($e, 'getStatusCode') ? $e->getStatusCode()
                    : (method_exists($e, 'getCode') ? ($e->getCode() ?: 500)
                    : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }

    public function list(Request $request, Response $response)
    {

        $result = $this->listDoFilter();
        $success = $result['success'];
        $message = $result['message'];
        $status = $result['status'];
        $list = $result['list'];
        $userType = $result['userType'];

        // Breadcrumbs
        $breadcrumbs = [
            ['description' => 'Solpeds', 'url' => null],
            ['description' => 'Monitor', 'url' => null]
        ];

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs,
                'userType' => $userType
            ]
        ], $status);
    }

    public function listFilter(Request $request, Response $response)
    {
        $filters = json_decode($request->getParsedBody()['Filters']);
        $result = $this->listDoFilter($filters);

        $success = $result['success'];
        $message = $result['message'];
        $status = $result['status'];
        $list = $result['list'];

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
            ]
        ], $status);
    }

    public function listDoFilter($filters = null)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [
            'ListaSolpedsEnPreparacion' => [],
            'ListaSolpedsEnAnalisis' => [],
            'ListaSolpedsAceptadas' => [],
            'ListaSolpedsDevueltas' => [],
            'ListaSolpedsRechazadas' => [],
            'ListaSolpedsAdjudicadas' => [],
            'ListaSolpedsCanceladas' => [],
            'DEBUG' => []
        ];

        // Archivo de log
        $logFile = __DIR__ . '/debug_solped.txt';
        $fp = fopen($logFile, 'a');
        fwrite($fp, "=== listDoFilter INICIO ===\n");

        try {
            $user = user();
            fwrite($fp, "Usuario: ID={$user->id}, type_id={$user->type_id}, customer_company={$user->customer_company->id}\n");

            // Sincronizar estados de solpeds con sus concursos adjudicados
            $this->syncSolpedAdjudicationStatus();

            //Created
            if ($user->type_id === 4){
                $created = $user->customer_company->getAllSolpedsByCompany()
                        ->where('id_solicitante', $user->id)
                        ->get();
                fwrite($fp, "Solpeds creados (type_id=4): " . count($created) . "\n");
            } else if ($user->type_id === 3){
                $created = $user->customer_company->getAllSolpedsByCompany()->get();
                fwrite($fp, "Solpeds creados (type_id=3): " . count($created) . "\n");
            } else if (isAdmin()) {
                $created = Solped::all();
                fwrite($fp, "Solpeds creados (admin): " . count($created) . "\n");
            }

            //En Analisis
            if (isAdmin()){
                $analiziyng = collect();
                fwrite($fp, "Admin -> analiziyng inicializado\n");
            } 

            //Created with trashed
            if (isAdmin()) {
                $created_with_trashed = Solped::withTrashed()->get();
            } else if ($user->type_id == 3 ) {
                $created_with_trashed = $user->customer_company->getAllSolpedsByCompany()->withTrashed()->get();
            } else if ($user->type_id == 4) {
                $created_with_trashed = $user->customer_company->getAllSolpedsByCompany()
                    ->where('id_solicitante', $user->id)
                    ->withTrashed()
                    ->get();
            } else {
                $created_with_trashed = collect();
            }
            fwrite($fp, "Solpeds con borrados incluidos: " . count($created_with_trashed) . "\n");

            //Deleted with trashed
            if (isAdmin()) {
                $deleted_with_trashed = Solped::where([
                    ['deleted_at', '!=', null]
                ])->get();
            } else if ($user->type_id == 3 ) {
                $deleted_with_trashed = $user->customer_company->getAllSolpedsByCompany()->withTrashed()->get();
            } else if ($user->type_id == 4) {
                $deleted_with_trashed = $user->customer_company->getAllSolpedsByCompany()
                    ->where('id_solicitante', $user->id)
                    ->withTrashed()
                    ->get();
            } else {
                $deleted_with_trashed = collect();
            }
            fwrite($fp, "Solpeds eliminados (with trashed): " . count($deleted_with_trashed) . "\n");

            //En Preparacion
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->etapa_actual === 'en-preparacion' && $solped->estado_actual === 'borrador';
            })->sortBy('id');

            fwrite($fp, "Solpeds en preparación: " . count($solpeds) . "\n");

            foreach ($solpeds as $solped) {
                fwrite($fp, "Solped ID: {$solped->id}, etapa: {$solped->etapa_actual}, estado: {$solped->estado_actual} - PREPARACION\n");
                $enable_send_to_buyer = $solped->productos->count() > 0;
                array_push(
                    $list['ListaSolpedsEnPreparacion'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [
                            'HabilitaEnvioAComprador' => $enable_send_to_buyer,
                            'Estado' => $solped->estado_actual,
                        ]
                    )
                );
            }

            //En analisis 

            //Esperando Revision (incluyendo esperando-revision-2)
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->estado_actual === 'esperando-revision' || $solped->estado_actual === 'esperando-revision-2';
            })->sortBy('id');

            fwrite($fp, "Solpeds esperando revision (incluyendo -2): " . count($solpeds) . "\n");

            foreach($solpeds as $solped) {
                fwrite($fp, "Solped ID: {$solped->id}, etapa: {$solped->etapa_actual}, estado: {$solped->estado_actual} - ANALISIS (esperando revision)\n");
                array_push(
                    $list['ListaSolpedsEnAnalisis'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [   
                            'FueRevisado' => in_array($solped->estado_actual, ['esperando-revision', 'esperando-revision-2']) ? false : true,
                            'Estado' => $solped->estado_actual
                        ]
                    )
                );
            }

            //Revisadas sin decision (incluyendo revisada-2)
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->estado_actual === 'revisada' || $solped->estado_actual === 'revisada-2';
            })->sortBy('id');

            fwrite($fp, "Solpeds revisadas (incluyendo -2): " . count($solpeds) . "\n");

            foreach($solpeds as $solped) {
                fwrite($fp, "Solped ID: {$solped->id}, etapa: {$solped->etapa_actual}, estado: {$solped->estado_actual} - ANALISIS (revisadas)\n");
                array_push(
                    $list['ListaSolpedsEnAnalisis'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [   
                            'FueRevisado' => in_array($solped->estado_actual, ['revisada', 'revisada-2']) ? true : false,
                            'Estado' => $solped->estado_actual
                        ]
                    )
                );
            }

            //Aceptadas/Aprobadas
            $estados_de_aceptadas = ['aceptada', 'aprobada', 'esperando-licitacion', 'licitando', 'licitacion-finalizada'];
            $solpeds = collect($created)->filter(function ($solped) use ($estados_de_aceptadas) {
                return in_array($solped->estado_actual, $estados_de_aceptadas) || $solped->etapa_actual === 'aprobada';
            })->sortBy('id');

            fwrite($fp, "Solpeds aceptadas/aprobadas: " . count($solpeds) . "\n");

            foreach ($solpeds as $solped) {
                fwrite($fp, "Solped ID: {$solped->id}, etapa: {$solped->etapa_actual}, estado: {$solped->estado_actual} - ACEPTADAS\n");
                array_push(
                    $list['ListaSolpedsAceptadas'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [
                            'FueAceptada' => true,
                            'Estado' => $solped->estado_actual,
                            'FechaAceptacion' => $solped->fecha_aceptacion ? $solped->fecha_aceptacion->format('d-m-Y H:i:s') : null
                        ]
                    )
                );
            }


            //Devueltas
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->etapa_actual === 'devuelta';
            })->sortBy('id');

            fwrite($fp, "Solpeds devueltas: " . count($solpeds) . "\n");

            foreach ($solpeds as $solped) {
                array_push(
                    $list['ListaSolpedsDevueltas'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [
                            'FueDevuelta' => $solped->etapa_actual === 'devuelta' ? true : false,
                            'ReturnComment' => $solped->return_comment ?: null,
                            'Estado' => $solped->estado_actual
                        ]
                    )
                );
            }

            //Rechazadas
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->etapa_actual === 'rechazada';
            })->sortBy('id');

            fwrite($fp, "Solpeds rechazadas: " . count($solpeds) . "\n");

            foreach ($solpeds as $solped) {
                array_push(
                    $list['ListaSolpedsRechazadas'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [
                            'FueRechazada' => $solped->etapa_actual === 'rechazada' ? true : false,
                            'RejectComment' => $solped->reject_comment ?: null,
                            'Estado' => $solped->estado_actual
                        ]
                    )
                );
            }

            // Adjudicadas
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->estado_actual === 'adjudicada' && $solped->etapa_actual === 'finalizada';
            })->sortBy('id');

            fwrite($fp, "Solpeds adjudicadas: " . count($solpeds) . "\n");

            foreach ($solpeds as $solped) {
                fwrite($fp, "Solped ID: {$solped->id}, etapa: {$solped->etapa_actual}, estado: {$solped->estado_actual} - ADJUDICADAS\n");
                array_push(
                    $list['ListaSolpedsAdjudicadas'],
                    $this->mapSolpedlist($solped)
                );
            }

            //Canceladas
            $solpeds = collect($created)->filter(function ($solped) {
                return $solped->etapa_actual === 'cancelada';
            })->sortBy('id');

            fwrite($fp, "Solpeds canceladas: " . count($solpeds) . "\n");

            foreach ($solpeds as $solped) {
                fwrite($fp, "Solped ID: {$solped->id}, etapa: {$solped->etapa_actual}, estado: {$solped->estado_actual} - CANCELADAS\n");
                array_push(
                    $list['ListaSolpedsCanceladas'],
                    array_merge(
                        $this->mapSolpedlist($solped),
                        [
                            'FueCancelada' => $solped->etapa_actual === 'cancelada' ? true : false,
                            'CancelMotive' => $solped->cancel_motive ?: null,
                            'Estado' => $solped->estado_actual,
                            'FechaCancelacion' => $solped->updated_at ? $solped->updated_at->format('d-m-Y H:i:s') : null
                        ]
                    )
                );
            }

            $success = true;
            fwrite($fp, "Ejecución correcta\n");
            fwrite($fp, "Lista:" . print_r($list, true) . "\n");

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
            fwrite($fp, "ERROR: " . $message . "\n");
        }

        fwrite($fp, "=== listDoFilter FIN ===\n\n");
        fclose($fp);

        return [
            'success' => $success,
            'message' => $message,
            'status' => $status,
            'list' => $list,
            'userType' => isset($user) ? $user->type_id : null
        ];
    }




    public function editOrCreate(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status  = 200;

        // ===== DEBUG =====
        $logFile = './solped_edit_or_create.log';
        $LOG = function ($label, $data = null) use ($logFile) {
            $fh = @fopen($logFile, 'a');
            if ($fh) {
                fwrite($fh, '[' . date('d-m-Y H:i:s') . "] $label");
                if ($data !== null) {
                    if ($data instanceof \Throwable) {
                        fwrite($fh, " EX: " . $data->getMessage() . " @ " . $data->getFile() . ":" . $data->getLine());
                        fwrite($fh, "\nTRACE: " . $data->getTraceAsString());
                    } else {
                        fwrite($fh, " DATA: " . print_r($data, true));
                    }
                }
                fwrite($fh, "\n");
                fclose($fh);
            }
        };
        // =================

        $LOG('START', [
            'uri'    => (string)$request->getUri(),
            'method' => $request->getMethod(),
            'params' => $params
        ]);

        $action  = $params['action'] ?? 'create';
        $create  = ($action === 'create');
        $id      = isset($params['id']) ? (int)$params['id'] : null;

        $LOG('ACTION/ID', ['action' => $action, 'create' => $create, 'id' => $id]);

        $filters = (object)[
            'categorias_con_areas_list'   => [],
            'areas'                       => [],
            'catalogo_de_materiales_list' => [],
            'material'                    => null,
            'paises_con_provincias_list'  => [],
            'provinces'                   => [],
            'cities_list'                 => [],
            'cities'                      => [],
        ];

        try {
            $user = user();
            $LOG('USER', ['id' => $user ? $user->id : null]);

            $solped   = null;
            $products = [];

            if (!$create) {
                if (!$id) {
                    $LOG('MISSING_ID');
                    throw new \Exception('Falta el ID de la Solped para edición.', 400);
                }

                $LOG('QUERY_SOLPED', ['id' => $id, 'id_solicitante' => $user->id]);
                $solped = \App\Models\Solped::where('id', $id)
                            ->where('id_solicitante', $user->id)
                            ->first();

                if (!$solped) {
                    $LOG('SOLPED_NOT_FOUND_OR_FORBIDDEN', ['id' => $id, 'user_id' => $user->id]);
                    throw new \Exception('Solped no encontrada o no pertenece a tu usuario.', 404);
                }

                $LOG('QUERY_ITEMS', ['id_solped' => $solped->id]);
                $items = \App\Models\SolpedItems::where('id_solped', $solped->id)
                            ->where('eliminado', '!=', 1)
                            ->get();
                $LOG('ITEMS_COUNT', ['count' => $items->count()]);

                $products = $items->map(function($it) {
                    return [
                        'id'                => (int)$it->id,
                        'name'              => (string)($it->nombre ?? ''),
                        'description'       => (string)($it->descripcion ?? ''),
                        'quantity'          => (float)($it->cantidad ?? 0),
                        'minimum_quantity'  => (float)($it->oferta_minima ?? 0),
                        'measurement_id'    => $it->unidad !== null ? (int)$it->unidad : null,
                        'targetcost'        => (float)($it->targetcost ?? 0),
                    ];
                })->all();
            }

            // Catálogos comunes
            $measurementList = \App\Models\Measurement::getList();
            $buyersList      = $user->getCompradoresByCompanyList();

            // === DOCS (un solo archivo) - SIEMPRE en storage/img/solpeds ===
            // Ruta absoluta en el servidor
            $diskDir = realpath(__DIR__ . '/../../../../storage/img/solpeds');

            // URL pública base (ajustá si tu "public" apunta a /)
            $publicBase = '/storage/img/solpeds/';


    $portraitFileName = '';
    $portraitUrl      = '';

    if (!$create && $solped) {
        // Traigo el último filename de solped_docs
        $portraitFileName = (string) \App\Models\SolpedDocument::where('solped_id', $solped->id)
                                ->orderBy('id', 'desc')
                                ->value('filename');

        $portraitFileName = trim($portraitFileName);

        if ($portraitFileName !== '') {
            // Normalizo por si guardaron ruta completa
            $bn = basename($portraitFileName);
            $abs = $diskDir . DIRECTORY_SEPARATOR . $bn;

            if (is_file($abs)) {
                $portraitFileName = $bn;                    // OK
                $portraitUrl      = $publicBase . $bn;      // URL pública
            } else {
                // No existe en disco: evito romper el preview
                $thisMissing = ['expected_abs' => $abs, 'filename' => $portraitFileName];
                // Logueo para debug
                if (isset($LOG)) { $LOG('DOC_MISSING_ON_DISK', $thisMissing); }
                $portraitFileName = '';
                $portraitUrl      = '';
            }
        }
    }

    // Log informativo
    if (isset($LOG)) { $LOG('DOCS', ['filename' => $portraitFileName, 'url' => $portraitUrl]); }


            // === Compradores sugeridos ===
            // Single (lo que usa el front actual)
            $compradorSugeridoId = null; // Por defecto 0
            if (!$create && $solped && !empty($solped->id_comprador_sugerido)) {
                $compradorSugeridoId = (int)$solped->id_comprador_sugerido;
            }

            // Multi (compatibilidad, si existiera pivot)
            $compradoresSelected = [];
            if (!$create && $solped && method_exists($solped, 'compradoresSugeridos')) {
                try {
                    $ids = $solped->compradoresSugeridos()->pluck('users.id');
                    if (is_object($ids) && method_exists($ids, 'all')) {
                        $ids = $ids->all();
                    }
                    $compradoresSelected = array_values(array_map('intval', (array)$ids));
                } catch (\Throwable $e) {
                    $LOG('BUYERS_PIVOT_WARN', $e->getMessage());
                    $compradoresSelected = [];
                }
            }
            // Si no hay pivot pero existe el single, lo reflejamos en el array por compatibilidad
            if (empty($compradoresSelected) && $compradorSugeridoId > 0) {
                $compradoresSelected = [$compradorSugeridoId];
            }

            $LOG('BUYERS_SELECTED', ['single' => $compradorSugeridoId, 'multi' => $compradoresSelected]);

            // Payload para KO
            $list = [
                'Id'                      => $create ? 0 : (int)$solped->id,
                'Nombre'                  => $create ? '' : (string)($solped->nombre ?? ''),
                'Descripcion'             => $create ? '' : (string)($solped->descripcion ?? ''),
                'CodigoInterno'           => $create ? '' : (string)($solped->codigo_interno ?? ''),
                'TipoCompraId'            => $create ? null : (isset($solped->tipo_compra) ? (int)$solped->tipo_compra : null),
                'AreaSolicitante'         => $create ? null : $this->getAreaIdByName($solped->area_sol ?? ''),
                'Pais'                    => $create ? null : ($solped->pais ?? null),
                'Provincia'               => $create ? '' : (string)($solped->provincia ?? ''),
                'Ciudad'                  => $create ? '' : (string)($solped->localidad ?? ''),
                'Direccion'               => $create ? '' : (string)($solped->direccion ?? ''),
                'Cp'                      => $create ? '' : (string)($solped->cp ?? ''),
                'Latitud'                 => $create ? '' : (string)($solped->latitud ?? ''),
                'Longitud'                => $create ? '' : (string)($solped->longitud ?? ''),
                'FechaAlta'               => $create
                                                ? \Carbon\Carbon::now()->format('d-m-Y H:i:s')
                                                : (
                                                    $solped->fecha_alta instanceof \Carbon\Carbon
                                                    ? $solped->fecha_alta->format('d-m-Y H:i:s')
                                                    : (string)$solped->fecha_alta
                                                ),
                'FechaResolucion'         => $create
                                                ? null
                                                : (
                                                    $solped->fecha_resolucion instanceof \Carbon\Carbon
                                                    ? $solped->fecha_resolucion->format('d-m-Y H:i')
                                                    : null
                                                ),
                'FechaEntrega'            => $create
                                                ? null
                                                : (
                                                    $solped->fecha_entrega instanceof \Carbon\Carbon
                                                    ? $solped->fecha_entrega->format('d-m-Y H:i')
                                                    : null
                                                ),
                'ProductMeasurementList'  => is_array($measurementList) ? $measurementList : (array)$measurementList,
                'CompradoresSugeridos'    => is_array($buyersList) ? $buyersList : (array)$buyersList,

                // >>> campos clave para el front:
                'CompradorSugeridoId'     => $compradorSugeridoId,     // single (0 si no hay)
                'CompradoresSugeridosSelected' => $compradoresSelected, // multi compat

                'Products'                => $create ? [] : $products,

                // Archivo (strings, nunca objetos)
                // Archivo (strings, nunca objetos)
                'DescripcionImagePath' => $portraitUrl ? $publicBase : '',   // ej: "/storage/img/solpeds/"
                'DescripcionPortrait'  => $portraitFileName,                 // ej: "6224c7e3b74d1dbc80c6...png"

            ];

            $LOG('LIST_KEYS', array_keys($list));
            $LOG('PRODUCTS_PREVIEW', ['count' => count($list['Products'])]);
            $LOG('DEBUG_SINGLE_RETURN', ['CompradorSugeridoId' => $list['CompradorSugeridoId']]);

            $breadcrumbs = [
                ['description' => 'Solped', 'url' => route('solped.serveList')],
                ['description' => $create ? 'Nuevo' : 'Edición']
            ];

            $success = true;

        } catch (\Throwable $e) {
            $LOG('EXCEPTION', $e);
            $success = false;
            $message = $e->getMessage();
            $status  = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : (method_exists($e, 'getCode') ? ($e->getCode() ?: 500) : 500);
            $list = [];
            $filters = (object)[];
            $breadcrumbs = [];
        }

        $LOG('END', ['success' => $success, 'status' => $status]);

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list'        => $list,
                'filters'     => $filters,
                'breadcrumbs' => $breadcrumbs
            ]
        ], $status);
    }



    public function store(Request $request, Response $response, $params)
{
    $success = false;
    $message = null;
    $status  = 200;

    // Debug liviano
    $logFile = './solped_store_debug.log';
    $log = function($label, $data = null) use ($logFile) {
        if (!$fh = @fopen($logFile, 'a')) return;
        fwrite($fh, "[".date('d-m-Y H:i:s')."] $label");
        if ($data !== null) fwrite($fh, " ".(is_string($data) ? $data : print_r($data, true)));
        fwrite($fh, "\n");
        fclose($fh);
    };

    try {
        // ---------- Parseo ----------
        $parsed = $request->getParsedBody();
        if (!isset($parsed['Data'])) {
            throw new \Exception('Payload inválido: falta Data.', 400);
        }
        $payload = json_decode($parsed['Data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error en JSON del payload', 400);
        }

        $action = strtolower($payload['Action'] ?? (isset($params['id']) ? 'edit' : 'create'));
        $id     = isset($payload['Id']) ? (int)$payload['Id'] : (isset($params['id']) ? (int)$params['id'] : 0);
        $entity = $payload['Entity'] ?? [];
        $user   = user();

        // ---------- Normalización ----------
        $codigoInterno = trim($entity['CodigoInterno'] ?? '');
        $nombre        = trim($entity['Nombre']        ?? '');
        $descripcion   = trim($entity['Descripcion']   ?? '');
        
        // Convertir ID de área a nombre de área
        $areaId = $entity['AreaSolicitante'] ?? '';
        $area_sol = $this->getAreaNameById($areaId);

        // Tipo de compra
        if (array_key_exists('TipoCompra', $entity)) {
            $tipoCompra = ($entity['TipoCompra'] === '' || $entity['TipoCompra'] === null) ? null : (int)$entity['TipoCompra'];
        } else {
            $tipoCompra = (array_key_exists('TipoCompraId', $entity) && $entity['TipoCompraId'] !== '')
                ? (int)$entity['TipoCompraId']
                : null;
        }

        $pais       = $entity['Pais']       ?? null;
        $provincia  = $entity['Provincia']  ?? null;
        $ciudad     = $entity['Ciudad']     ?? ($entity['Localidad'] ?? null);
        $direccion  = $entity['Direccion']  ?? null;
        $cp         = $entity['Cp']         ?? null;
        $latitud    = $entity['Latitud']    ?? null;
        $longitud   = $entity['Longitud']   ?? null;

        // --- Comprador sugerido (normalizado a NULL si no hay valor válido) ---
        $idCompradorSugerido = null;
        if (array_key_exists('CompradorSugeridoSelected', $entity)) {
            $v = $entity['CompradorSugeridoSelected'];
            $idCompradorSugerido = (is_numeric($v) && (int)$v > 0) ? (int)$v : null;
        } elseif (array_key_exists('CompradorSugeridoId', $entity)) {
            $v = $entity['CompradorSugeridoId'];
            $idCompradorSugerido = (is_numeric($v) && (int)$v > 0) ? (int)$v : null;
        } elseif (!empty($entity['CompradoresSugeridosSelected']) && is_array($entity['CompradoresSugeridosSelected'])) {
            $first = reset($entity['CompradoresSugeridosSelected']);
            $idCompradorSugerido = (is_numeric($first) && (int)$first > 0) ? (int)$first : null;
        }

        // --- Fechas de resolución y entrega ---
        $fechaResolucion = null;
        if (!empty($entity['FechaResolucion'])) {
            try {
                $fechaResolucion = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $entity['FechaResolucion']);
                $fechaResolucion->setSeconds(0); // Asegurar que se guarden los segundos
            } catch (\Exception $e) {
                try {
                    $fechaResolucion = \Carbon\Carbon::parse($entity['FechaResolucion']);
                    $fechaResolucion->setSeconds(0);
                } catch (\Exception $e2) {
                    $fechaResolucion = null;
                }
            }
        }

        $fechaEntrega = null;
        if (!empty($entity['FechaEntrega'])) {
            try {
                $fechaEntrega = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $entity['FechaEntrega']);
                $fechaEntrega->setSeconds(0); // Asegurar que se guarden los segundos
            } catch (\Exception $e) {
                try {
                    $fechaEntrega = \Carbon\Carbon::parse($entity['FechaEntrega']);
                    $fechaEntrega->setSeconds(0);
                } catch (\Exception $e2) {
                    $fechaEntrega = null;
                }
            }
        }

        // Items
        $itemsInput = $entity['Products'] ?? [];
        $items = [];
        foreach ($itemsInput as $p) {
            $items[] = [
                'id'             => isset($p['id']) ? (int)$p['id'] : 0,
                'nombre'         => (string)($p['name'] ?? ''),
                'descripcion'    => (string)($p['description'] ?? ''),
                'cantidad'       => isset($p['quantity']) ? (float)$p['quantity'] : 0.0,
                'oferta_minima'  => isset($p['minimum_quantity']) ? (float)$p['minimum_quantity'] : 0.0,
                'unidad'         => isset($p['measurement_id']) ? (int)$p['measurement_id'] : null,
                'targetcost'     => isset($p['targetcost']) ? (float)$p['targetcost'] : 0.0,
            ];
        }

        // Documentos
        $docs = [];
        if (!empty($entity['Documents']) && is_array($entity['Documents'])) {
            foreach ($entity['Documents'] as $d) {
                if (is_string($d) && $d !== '') $docs[] = $d;
                if (is_array($d) && !empty($d['filename'])) $docs[] = (string)$d['filename'];
            }
        }
        if (!empty($entity['DescripcionPortrait']['filename'])) {
            $docs[] = (string)$entity['DescripcionPortrait']['filename'];
        }
        if (!empty($entity['Portrait']['filename'])) {
            $docs[] = (string)$entity['Portrait']['filename'];
        }
        $docs = array_values(array_unique(array_filter(array_map('strval', $docs), function($s){ return trim($s) !== ''; })));

        $log('normalized', [
            'action' => $action,
            'id'     => $id,
            'nombre' => $nombre,
            'area_sol' => $area_sol,
            'tipoCompra' => $tipoCompra,
            'id_comprador_sugerido' => $idCompradorSugerido,
            'items'  => count($items),
            'docs'   => count($docs)
        ]);

        // ---------- Validación mínima ----------
        $validItems = array_filter($items, function ($row) {
            return ($row['nombre'] !== '') && !empty($row['unidad']);
        });

        if ($nombre === '' || !$tipoCompra || $area_sol === '' || !$fechaResolucion || !$fechaEntrega || count($validItems) === 0) {
            throw new \Exception('Faltan campos obligatorios (Nombre, Área solicitante, Tipo de compra, Fechas de resolución/entrega y al menos un ítem).', 422);
        }

            // Regla adicional: FechaResolucion < FechaEntrega y al menos 7 días de diferencia
            if ($fechaResolucion instanceof \DateTimeInterface && $fechaEntrega instanceof \DateTimeInterface) {
                $diffSeconds = $fechaEntrega->getTimestamp() - $fechaResolucion->getTimestamp();
                if ($diffSeconds <= 0) {
                    throw new \Exception('La fecha de resolución debe ser anterior a la fecha de entrega.', 400);
                }
                $diffDays = $diffSeconds / (60 * 60 * 24);
                if ($diffDays < 7) {
                    throw new \Exception('La diferencia entre resolución y entrega debe ser de al menos 7 días.', 400);
                }
            }

        // ---------- Transacción ----------
        $capsule    = dependency('db');
        $connection = $capsule->getConnection();
        $connection->beginTransaction();
        $log('tx-start');

        if ($action === 'create' || $id === 0) {
            // CREATE
            $solped = new \App\Models\Solped();
            $solped->id_solicitante = $user->id;
            $solped->codigo_interno = $codigoInterno;
            $solped->area_sol       = $area_sol;
            $solped->nombre         = $nombre;
            $solped->descripcion    = $descripcion;
            $solped->pais           = $pais;
            $solped->provincia      = $provincia;
            $solped->localidad      = $ciudad;
            $solped->direccion      = $direccion;
            $solped->cp             = $cp;
            $solped->latitud        = $latitud;
            $solped->longitud       = $longitud;
            $solped->tipo_compra    = $tipoCompra;
            $solped->etapa_actual   = 'en-preparacion';
            $solped->estado_actual  = 'borrador';
            $solped->id_comprador_sugerido = $idCompradorSugerido; // ahora siempre NULL o un int válido
            $solped->fecha_resolucion = $fechaResolucion;
            $solped->fecha_entrega    = $fechaEntrega;
            $solped->fecha_alta     = \Carbon\Carbon::now();

            $solped->save();

            // Items
            foreach ($items as $row) {
                if ($row['nombre'] === '' || !$row['unidad']) { continue; }
                $it = new \App\Models\SolpedItems();
                $it->id_solped          = $solped->id;
                $it->id_usuario_creador = $user->id;
                $it->nombre             = $row['nombre'];
                $it->descripcion        = $row['descripcion'];
                $it->cantidad           = $row['cantidad'];
                $it->oferta_minima      = $row['oferta_minima'];
                $it->unidad             = $row['unidad'];
                $it->targetcost         = $row['targetcost'];
                $it->eliminado          = 0;
                $it->save();
            }

            // Documentos
            foreach ($docs as $filename) {
                \App\Models\SolpedDocument::create([
                    'solped_id' => $solped->id,
                    'filename'  => $filename,
                ]);
            }

            // Mover archivos desde directorio temporal al directorio final de solpeds
            if (!empty($docs)) {
                $targetDir = rootPath() . '/storage/img/solpeds/';
                
                // Crear directorio si no existe
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                foreach ($docs as $filename) {
                    // Buscar archivo en posibles ubicaciones temporales
                    $tempPaths = [
                        rootPath() . '/storage/temp/' . $filename,
                        rootPath() . '/uploads/' . $filename,
                        rootPath() . '/storage/uploads/' . $filename,
                        rootPath() . '/storage/img/solpeds/' . $filename // Ya está en el lugar correcto
                    ];
                    
                    $sourcePath = null;
                    foreach ($tempPaths as $path) {
                        if (file_exists($path)) {
                            $sourcePath = $path;
                            break;
                        }
                    }
                    
                    if ($sourcePath && !strpos($sourcePath, '/storage/img/solpeds/')) {
                        $targetPath = $targetDir . $filename;
                        
                        // Mover archivo al directorio final
                        if (rename($sourcePath, $targetPath)) {
                            $log('file-moved', ['from' => $sourcePath, 'to' => $targetPath]);
                        } else {
                            $log('file-move-failed', ['source' => $sourcePath, 'target' => $targetPath]);
                        }
                    } elseif (!$sourcePath) {
                        $log('file-not-found', ['filename' => $filename, 'searched' => $tempPaths]);
                    }
                }
            }

            $connection->commit();
            $success = true;
            $message = 'Solicitud de pedido creada con éxito.';
            $log('create-ok', ['id'=>$solped->id, 'items'=>count($items), 'docs'=>count($docs)]);
        } else {
            // EDIT
            $solped = \App\Models\Solped::where('id_solicitante', $user->id)->find($id);
            if (!$solped) {
                throw new \Exception('Solped no encontrada o no pertenece al usuario.', 404);
            }

            $solped->codigo_interno = $codigoInterno;
            $solped->area_sol       = $area_sol;
            $solped->nombre         = $nombre;
            $solped->descripcion    = $descripcion;
            $solped->pais           = $pais;
            $solped->provincia      = $provincia;
            $solped->localidad      = $ciudad;
            $solped->direccion      = $direccion;
            $solped->cp             = $cp;
            $solped->latitud        = $latitud;
            $solped->longitud       = $longitud;
            $solped->tipo_compra    = $tipoCompra;
            $solped->id_comprador_sugerido = $idCompradorSugerido; // null o int válido
            $solped->fecha_resolucion      = $fechaResolucion;
            $solped->fecha_entrega         = $fechaEntrega;
            $solped->save();

            // Items reconciliation - Actualizar/crear/eliminar items
            $existingItems = \App\Models\SolpedItems::where('id_solped', $solped->id)
                                                   ->where('eliminado', '!=', '1')
                                                   ->get()
                                                   ->keyBy('id');
            $submittedItemIds = [];

            // Procesar items enviados desde el frontend
            foreach ($items as $row) {
                if ($row['nombre'] === '' || !$row['unidad']) { 
                    continue; 
                }

                $itemId = $row['id'];
                $submittedItemIds[] = $itemId;

                if ($itemId > 0 && $existingItems->has($itemId)) {
                    // ACTUALIZAR item existente
                    $existingItem = $existingItems->get($itemId);
                    $existingItem->nombre        = $row['nombre'];
                    $existingItem->descripcion   = $row['descripcion'];
                    $existingItem->cantidad      = $row['cantidad'];
                    $existingItem->oferta_minima = $row['oferta_minima'];
                    $existingItem->unidad        = $row['unidad'];
                    $existingItem->targetcost    = $row['targetcost'];
                    $existingItem->save();
                    $log('item-updated', ['item_id' => $itemId, 'nombre' => $row['nombre']]);
                } else {
                    // CREAR nuevo item
                    $newItem = new \App\Models\SolpedItems();
                    $newItem->id_solped          = $solped->id;
                    $newItem->id_usuario_creador = $user->id;
                    $newItem->nombre             = $row['nombre'];
                    $newItem->descripcion        = $row['descripcion'];
                    $newItem->cantidad           = $row['cantidad'];
                    $newItem->oferta_minima      = $row['oferta_minima'];
                    $newItem->unidad             = $row['unidad'];
                    $newItem->targetcost         = $row['targetcost'];
                    $newItem->eliminado          = 0;
                    $newItem->save();
                    $log('item-created', ['item_id' => $newItem->id, 'nombre' => $row['nombre']]);
                }
            }

            // Marcar como eliminados los items que ya no están en la lista
            foreach ($existingItems as $existingItem) {
                if (!in_array($existingItem->id, $submittedItemIds)) {
                    $existingItem->eliminado = 1;
                    $existingItem->save();
                    $log('item-deleted', ['item_id' => $existingItem->id, 'nombre' => $existingItem->nombre]);
                }
            }

            // Documentos - actualizar si es necesario
            if (!empty($docs)) {
                // Eliminar documentos existentes
                \App\Models\SolpedDocument::where('solped_id', $solped->id)->delete();
                
                // Agregar nuevos documentos
                foreach ($docs as $filename) {
                    \App\Models\SolpedDocument::create([
                        'solped_id' => $solped->id,
                        'filename'  => $filename,
                    ]);
                }
            }

            $connection->commit();
            $success = true;
            $message = 'Solicitud de pedido actualizada con éxito.';
            $log('edit-ok', [
                'id'=>$solped->id,
                'items_processed'=>count($items),
                'comprador_sugerido'=>$idCompradorSugerido
            ]);
        }

    } catch (\Throwable $e) {
        if (isset($connection) && method_exists($connection, 'transactionLevel') && $connection->transactionLevel() > 0) {
            try { $connection->rollBack(); } catch (\Throwable $e2) {}
        }
        $log('EXCEPTION', $e);
        error_log('[SOLPED][store] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());

        $success = false;
        $message = $e->getMessage();
        $status  = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : (method_exists($e, 'getCode') ? ($e->getCode() ?: 500) : 500);
    }

    return $this->json($response, [
        'success' => $success,
        'message' => $message,
        'data'    => ['redirect' => '']
    ], $status);
}


    public function send(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;

        // Log para debugging
        error_log("=== SOLPED SEND DEBUG ===");
        error_log("Request body: " . json_encode($request->getParsedBody()));
        error_log("Params: " . json_encode($params));

        try {
            $user = user();
            error_log("User ID: " . $user->id);
            
            // Obtener ID desde parámetros o desde el body
            $id = isset($params['id']) ? (int)$params['id'] : null;
            
            // Si no viene en params, intentar obtenerlo del body
            if (!$id) {
                $parsed = $request->getParsedBody();
                $id = isset($parsed['IdSolped']) ? (int)$parsed['IdSolped'] : null;
            }

            error_log("Solped ID: " . $id);

            if (!$id) {
                throw new \Exception('ID de Solped requerido.', 400);
            }

            $solped = \App\Models\Solped::where('id', $id)
                ->where('id_solicitante', $user->id)
                ->first();

            if (!$solped) {
                error_log("Solped no encontrada para ID: $id, User: " . $user->id);
                throw new \Exception('Solped no encontrada o no te pertenece.', 404);
            }

            error_log("Solped encontrada: " . $solped->id . ", etapa: " . $solped->etapa_actual . ", estado: " . $solped->estado_actual);

            // Validar que tenga al menos un item
            $productCount = $solped->productos()->where('eliminado', '!=', 1)->count();
            error_log("Productos count: " . $productCount);
            
            if ($productCount === 0) {
                throw new \Exception('La Solped debe tener al menos un ítem antes de enviarla.', 400);
            }

            // Validar que esté en etapa correcta para envío
            $allowedStates = [
                ['etapa' => 'en-preparacion', 'estado' => 'borrador'],
                ['etapa' => 'devuelta', 'estado' => 'devuelta']
            ];
            
            $canSend = false;
            $isResend = false;
            
            foreach ($allowedStates as $allowedState) {
                if ($solped->etapa_actual === $allowedState['etapa'] && $solped->estado_actual === $allowedState['estado']) {
                    $canSend = true;
                    if ($allowedState['etapa'] === 'devuelta') {
                        $isResend = true;
                    }
                    break;
                }
            }
            
            if (!$canSend) {
                error_log("Estado/etapa inválida: etapa=" . $solped->etapa_actual . ", estado=" . $solped->estado_actual);
                throw new \Exception('Solo se pueden enviar Solpeds que estén en estado "Borrador" o "Devuelta".', 400);
            }

            error_log("Tipo de envío: " . ($isResend ? 'Reenvío (corregida)' : 'Envío inicial'));

            // Cambiar estado y etapa
            $solped->etapa_actual = 'en-analisis';
            
            // Si es un reenvío (estaba devuelta), usar estado especial
            if ($isResend) {
                $solped->estado_actual = 'esperando-revision-2';
            } else {
                $solped->estado_actual = 'esperando-revision';
            }
            
            // Actualizar fecha de envío
            $solped->fecha_envio_a_comprador = \Carbon\Carbon::now();
            
            // Si es un reenvío, limpiar campos de devolución
            if ($isResend) {
                $solped->return_comment = null;
                $solped->fecha_devolucion = null;
                $solped->id_comprador_decision = null;
            }
            
            $solped->save();

            error_log("Solped actualizada correctamente");

            // Enviar emails a compradores
            $this->sendSolpedEmails($solped, $user);

            $success = true;
            $message = $isResend 
                ? 'Solicitud corregida reenviada correctamente para revisión.' 
                : 'Solicitud enviada correctamente para revisión.';

        } catch (\Throwable $e) {
            $success = false;
            $message = $e->getMessage();
            
            error_log("Error en send: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            
            // Manejar códigos de estado HTTP correctamente
            if (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            } elseif (method_exists($e, 'getCode') && $e->getCode() > 0) {
                $code = $e->getCode();
                // Validar que sea un código HTTP válido
                if ($code >= 100 && $code <= 599) {
                    $status = $code;
                } else {
                    $status = 500;
                }
            } else {
                $status = 500;
            }
        }

        error_log("Response: success=$success, status=$status, message=$message");
        error_log("=== END SOLPED SEND DEBUG ===");

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => $success ? '/solped/solicitante/monitor' : null
            ]
        ], $status);
    }


    public function listJson(Request $request, Response $response)
    {
        try {
            $user = user();

            // Trae solpeds del usuario (ajustá el scope a tu regla de negocio)
            $rows = \App\Models\Solped::query()
                ->where('id_solicitante', $user->id)
                ->orderBy('id', 'desc')
                ->get(['id','nombre','id_solicitante']);

        $list = $rows->map(function ($r) {
                $u = \App\Models\User::find($r->id_solicitante); // ojo: N+1 (ver nota abajo)

                $full = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                $creadoPor = $full !== ''
                    ? $full
                    : (($u->username ?? '') !== '' ? $u->username : ($u->email ?? '—'));

                return [
                    'Id'        => (int) $r->id,
                    'Nombre'    => (string) $r->nombre,
                    'CreadoPor' => $creadoPor,
                ];
            })->all();

            return $this->json($response, [
                'success' => true,
                'data' => [
                    'list'        => $list,
                    'breadcrumbs' => [
                        ['description' => 'Solped', 'url' => route('solped.serveList')],
                        ['description' => 'Listado']
                    ],
                ]
            ], 200);

        } catch (\Throwable $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request, Response $response, $params)
    {
        try {
            $user = user();
            $id = (int)($params['id'] ?? 0);

            $solped = \App\Models\Solped::where('id', $id)
                ->where('id_solicitante', $user->id)
                ->first();

            if (!$solped) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Solped no encontrada o no te pertenece.'
                ], 404);
            }

            $solped->delete();

            return $this->json($response, [
                'success' => true,
                'message' => 'Solicitud eliminada correctamente.',
                'data' => ['redirect' => true]
            ], 200);

        } catch (\Throwable $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    private function mapSolpedList($solped)
        {
            // $file = fopen("messi.txt", "w");
            // fwrite($file, json_encode($solped->getAttributes()));
            // fclose($file);
            
            $data = [
                'Id' => $solped->id,
                'Nombre' => $solped->nombre,
                'Solicitante' => $solped->solicitante->full_name,
                'AreaSolicitante' => $solped->area_sol,
                'CodigoInterno' => $solped->codigo_interno, 
                'Urgencia' => $solped->tipo_compra_nombre,
                'Estado' => $solped->estado_actual,
                'Etapa' => $solped->etapa_actual,
            ];
            
            // Si está adjudicada, agregar detalles de adjudicación
            if ($solped->estado_actual === 'adjudicada') {
                $data['AdjudicacionDetalles'] = [
                    'adjudicada' => true,
                    'empresaAdjudicada' => null,
                ];
                
                // Obtener el concurso desde created_from_solped si existe
                // Necesitamos buscar el concurso que contiene este ID de solped
                $concurso = \App\Models\Concurso::whereRaw("FIND_IN_SET(?, created_from_solped)", [$solped->id])->first();
                
                if ($concurso) {
                    // Buscar oferente con etapa 'adjudicacion-aceptada'
                    $adjudicado = $concurso->oferentes
                        ->where('etapa_actual', \App\Models\Participante::ETAPAS['adjudicacion-aceptada'])
                        ->first();
                    
                    if ($adjudicado && $adjudicado->company) {
                        $data['AdjudicacionDetalles']['empresaAdjudicada'] = $adjudicado->company->business_name;
                        $data['AdjudicacionDetalles']['idConcurso'] = $concurso->id;
                        $data['AdjudicacionDetalles']['nombreConcurso'] = $concurso->nombre;
                    }
                }
            }
            
            return $data;
    }

    /**
     * Convierte el ID de área al nombre correspondiente
     */
    private function getAreaNameById($areaId) 
    {
        // Si ya es un string (nombre), lo devolvemos tal como está
        if (!is_numeric($areaId)) {
            return trim($areaId);
        }

        // Mapeo de IDs a nombres de área (debe coincidir con el frontend)
        $areas = [
            1 => 'Administración',
            2 => 'Comercial', 
            3 => 'Compras',
            4 => 'Almacenes',
            5 => 'Logística',
            6 => 'Produccion',
            7 => 'Mantenimiento',
            8 => 'Calidad',
            9 => 'Seguridad de las Personas',
            10 => 'Medio Ambiente',
            11 => 'Oficina Técnica',
            12 => 'Informática'
        ];

        $id = (int) $areaId;
        return isset($areas[$id]) ? $areas[$id] : '';
    }

    /**
     * Convierte el nombre de área al ID correspondiente
     */
    private function getAreaIdByName($areaName) 
    {
        // Si ya es numérico (ID), lo devolvemos tal como está
        if (is_numeric($areaName)) {
            return (int) $areaName;
        }

        // Mapeo de nombres a IDs de área (debe coincidir con el frontend)
        $areas = [
            'Administración' => 1,
            'Comercial' => 2,
            'Compras' => 3,
            'Almacenes' => 4,
            'Logística' => 5,
            'Produccion' => 6,
            'Mantenimiento' => 7,
            'Calidad' => 8,
            'Seguridad de las Personas' => 9,
            'Medio Ambiente' => 10,
            'Oficina Técnica' => 11,
            'Informática' => 12
        ];

        $name = trim($areaName);
        return isset($areas[$name]) ? $areas[$name] : null;
    }

    public function cancelSolped(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;

        // Log para debugging
        error_log("=== SOLPED CANCEL DEBUG ===");
        error_log("Request body: " . json_encode($request->getParsedBody()));
        error_log("Params: " . json_encode($params));

        try {
            $user = user();
            error_log("User ID: " . $user->id);
            
            // Obtener datos del request
            $parsed = $request->getParsedBody();
            $id = isset($parsed['IdSolped']) ? (int)$parsed['IdSolped'] : null;
            $justificacion = isset($parsed['Justificacion']) ? trim($parsed['Justificacion']) : null;

            error_log("Solped ID: " . $id);
            error_log("Justificacion: " . $justificacion);

            // Validaciones
            if (!$id) {
                throw new \Exception('ID de Solped requerido.', 400);
            }

            if (!$justificacion || strlen($justificacion) < 10) {
                throw new \Exception('La justificación debe tener al menos 10 caracteres.', 400);
            }

            // Buscar la solicitud
            $solped = \App\Models\Solped::where('id', $id)
                ->where('id_solicitante', $user->id)
                ->first();

            if (!$solped) {
                error_log("Solped no encontrada para ID: $id, User: " . $user->id);
                throw new \Exception('Solped no encontrada o no te pertenece.', 404);
            }

            error_log("Solped encontrada: " . $solped->id . ", etapa: " . $solped->etapa_actual . ", estado: " . $solped->estado_actual);

            // Validar que la solicitud se pueda cancelar
            $cancelableStates = [
                ['etapa' => 'en-preparacion', 'estado' => 'borrador'],
                ['etapa' => 'en-analisis', 'estado' => 'esperando-revision'],
                ['etapa' => 'en-analisis', 'estado' => 'esperando-revision-2'],
                ['etapa' => 'devuelta', 'estado' => 'devuelta']
            ];
            
            $canCancel = false;
            foreach ($cancelableStates as $cancelableState) {
                if ($solped->etapa_actual === $cancelableState['etapa'] && $solped->estado_actual === $cancelableState['estado']) {
                    $canCancel = true;
                    break;
                }
            }
            
            if (!$canCancel) {
                error_log("Estado/etapa no cancelable: etapa=" . $solped->etapa_actual . ", estado=" . $solped->estado_actual);
                throw new \Exception('Solo se pueden cancelar solicitudes que estén en estado "Borrador", "Esperando Revisión" o "Devuelta".', 400);
            }

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Actualizar estado de la solicitud
                $solped->etapa_actual = 'cancelada';
                $solped->estado_actual = 'cancelada';
                $solped->cancel_motive = $justificacion;
                $solped->updated_at = \Carbon\Carbon::now();
                
                $solped->save();

                // Confirmar transacción
                DB::commit();

                error_log("Solped cancelada correctamente");

                $success = true;
                $message = 'Solicitud cancelada correctamente.';

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Throwable $e) {
            $success = false;
            $message = $e->getMessage();
            
            error_log("Error en cancelSolped: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            
            // Manejar códigos de estado HTTP correctamente
            if (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            } elseif (method_exists($e, 'getCode') && $e->getCode() > 0) {
                $code = $e->getCode();
                // Validar que sea un código HTTP válido
                if ($code >= 100 && $code <= 599) {
                    $status = $code;
                } else {
                    $status = 500;
                }
            } else {
                $status = 500;
            }
        }

        error_log("Response: success=$success, status=$status, message=$message");
        error_log("=== END SOLPED CANCEL DEBUG ===");

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => $success ? '/solped/solicitante/monitor' : null
            ]
        ], $status);
    }

    /**
     * Enviar emails a los compradores cuando se envía una Solped
     * 
     * @param \App\Models\Solped $solped
     * @param \App\Models\User $solicitante
     */
    private function sendSolpedEmails($solped, $solicitante)
    {
        try {
            error_log("=== ENVIANDO EMAILS DE SOLPED ===");
            
            // Obtener los emails de los compradores
            $compradores = [];
            
            // Si hay comprador sugerido, enviar solo a ese
            if ($solped->id_comprador_sugerido) {
                error_log("Enviando a comprador sugerido: " . $solped->id_comprador_sugerido);
                $comprador = User::find($solped->id_comprador_sugerido);
                if ($comprador && $comprador->email) {
                    $compradores[] = $comprador;
                }
            } else {
                // Si no hay comprador sugerido, enviar a todos los compradores de la empresa
                error_log("Obteniendo todos los compradores de la empresa");
                $empresa = $solicitante->customer_company_id;
                
                if ($empresa) {
                    $compradores = User::where('type_id', 3)
                        ->where('customer_company_id', $empresa)
                        ->where('status_id', 1) // Solo usuarios activos
                        ->get();
                    
                    error_log("Compradores encontrados: " . $compradores->count());
                }
            }
            
            if (empty($compradores)) {
                error_log("No hay compradores para enviar emails");
                return;
            }
            
            // Preparar datos para la plantilla
            $emailData = [
                'nombreSolicitud' => $solped->nombre,
                'areaSolicitante' => $solped->area_sol,
                'fechaResolucion' => $solped->fecha_resolucion ? $solped->fecha_resolucion->format('d-m-Y H:i') : 'No especificada',
                'enlaceAcceso' => env('APP_SITE_URL') 
            ];
            
            // Enviar emails a cada comprador
            $emailService = new EmailService();
            
            foreach ($compradores as $comprador) {
                try {
                    error_log("Enviando email a: " . $comprador->email);
                    
                    $emailData['compradorNombre'] = $comprador->full_name;
                    $subject = 'Nueva Solicitud de Pedido: ' . $solped->nombre;
                    $emailData['title'] = $subject;
                    
                    // Renderizar la plantilla Smarty
                    $html = $this->renderEmailTemplate('solped-sent.tpl', $emailData);
                    
                    // Enviar email
                    $result = $emailService->send(
                        $html,
                        $subject,
                        [$comprador->email],
                        $comprador->full_name
                    );
                    
                    if ($result) {
                        error_log("Email enviado correctamente a: " . $comprador->email);
                    } else {
                        error_log("Fallo al enviar email a: " . $comprador->email);
                    }
                } catch (\Exception $e) {
                    error_log("Error enviando email a " . $comprador->email . ": " . $e->getMessage());
                }
            }
            
            error_log("=== FIN ENVÍO DE EMAILS DE SOLPED ===");
            
        } catch (\Exception $e) {
            error_log("Error en sendSolpedEmails: " . $e->getMessage());
        }
    }

    /**
     * Renderizar una plantilla de email con datos
     * 
     * @param string $template
     * @param array $data
     * @return string HTML renderizado
     */
    private function renderEmailTemplate($template, $data)
    {
        // Asignar año global
        $data['ano'] = date('Y');
        
        // Renderizar la plantilla usando el método fetch del BaseController
        return $this->fetch('email/' . $template, $data);
    }

    /**
     * Sincronizar estados de solpeds basado en adjudicación de concursos
     * Si una solped está asociada a un concurso que tiene oferentes con adjudicación aceptada,
     * actualizar la solped a estado 'adjudicada' y etapa 'finalizada'
     */
    private function syncSolpedAdjudicationStatus()
    {
        try {
            // Obtener todos los concursos que tienen oferentes con adjudicación aceptada
            $concursosAdjudicados = \App\Models\Concurso::whereHas('oferentes', function($query) {
                $query->where('etapa_actual', 'adjudicacion-aceptada');
            })->get();

            error_log("Sincronizando solpeds: encontrados " . count($concursosAdjudicados) . " concursos adjudicados");

            foreach ($concursosAdjudicados as $concurso) {
                // created_from_solped contiene IDs de solpeds separadas por comas
                if (!empty($concurso->created_from_solped)) {
                    $solpedIds = array_map('intval', array_filter(explode(',', $concurso->created_from_solped)));
                    
                    if (!empty($solpedIds)) {
                        // Actualizar solpeds a estado 'adjudicada' si aún no están en ese estado
                        \App\Models\Solped::whereIn('id', $solpedIds)
                            ->where('estado_actual', '!=', 'adjudicada')
                            ->update([
                                'estado_actual' => 'adjudicada',
                                'etapa_actual' => 'finalizada'
                            ]);

                        error_log("Solpeds actualizadas a adjudicada para concurso {$concurso->id}: " . implode(',', $solpedIds));
                    }
                }
            }

            error_log("=== FIN SINCRONIZACIÓN DE ADJUDICACIONES ===");

        } catch (\Exception $e) {
            error_log("Error en syncSolpedAdjudicationStatus: " . $e->getMessage());
        }
    }




}
