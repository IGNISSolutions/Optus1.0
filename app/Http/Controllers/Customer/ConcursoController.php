<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;
use App\Models\InvitationStatus;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\UserType;
use Carbon\Traits\ToStringFormat;
use PhpMyAdmin\Console;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\Go;
use App\Models\Pais;
use App\Models\Category;
use App\Models\Moneda;
use App\Models\Measurement;
use App\Models\Catalogo;
use App\Models\GoType;
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
use App\Models\GoDocument;
use App\Models\GoDocumentAdditional;
use App\Models\GoLoadType;
use App\Models\GoPaymentMethod;
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

class ConcursoController extends BaseController
{
    protected static $description_limit = 5000;

    public function serveList(Request $request, Response $response)
    {
        return $this->render($response, 'concurso/list/cliente/type-list.tpl', [
            'page' => 'concursos',
            'accion' => 'listado-cliente',
            'tipo' => 'cliente',
            'title' => 'Monitor - Cliente'
        ]);
    }

    public function serveTypeList(Request $request, Response $response, $params)
    {
        $title = null;
        switch ($params['type']) {
            case Concurso::TYPES['online']:
                $title = 'Listado de Subastas';
                break;
            case Concurso::TYPES['sobrecerrado']:
                $title = 'Listado de Licitaciones';
                break;
            case Concurso::TYPES['go']:
                $title = 'Concursos Go Listado';
                break;
        }

        return $this->render($response, 'concurso/list/list.tpl', [
            'page' => 'concursos',
            'accion' => 'listado-' . $params['type'],
            'tipo' => $params['type'],
            'title' => $title
        ]);
    }

    public function serveDetail(Request $request, Response $response, $params)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $secret = getenv('TOKEN_SECRET_KEY');

        $id = (int) $params['id'];
        $sessionId = session_id();
        $expectedToken = hash_hmac('sha256', $id . $sessionId, $secret);
        $storedToken   = $_SESSION['edit_token'][$id] ?? null;

        if (!$storedToken || $expectedToken !== $storedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido para ver el detalle del concurso'
            ], 403);
        }

        // No eliminamos el token para permitir F5
        // unset($_SESSION['edit_token'][$id]);

        if (isAdmin()) {
            $concurso = Concurso::find($id);
        } else {
            $user = user();
            $concurso = $user->customer_company->getAllConcursosByCompany()->find($id)
                    ?? $user->concursos_evalua->find($id);
        }

        abort_if($request, $response, !$concurso, true, 404);

        $title = getStepName($params['step'], $params['type'] === Concurso::TYPES['go']);

        return $this->render($response, 'concurso/detail/customer/detail.tpl', [
            'page' => 'concursos',
            'accion' => 'poretapascliente',
            'tipo_concurso' => $params['type'],
            'tipo' => $params['step'],
            'idConcurso' => $params['id'],
            'title' => $title
        ]);
    }


    public function serveEdit(Request $request, Response $response, $params)
    {   
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $secret = getenv('TOKEN_SECRET_KEY');

        $id = (int) $params['id'];
        $sessionId = session_id();
        $expectedToken = hash_hmac('sha256', $id . $sessionId, $secret);
        $storedToken   = $_SESSION['edit_token'][$id] ?? null;
        
        if (!$storedToken || $expectedToken !== $storedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido para editar el concurso.'
            ], 403);
        }

        //  No lo eliminamos para permitir F5
        // unset($_SESSION['edit_token'][$id]);

        $concurso = user()->customer_company->getAllConcursosByCompany()->find($id);
        abort_if($request, $response, !$concurso, true, 404);

        $title = null;
        $description = null;
        switch ($params['type']) {
            case Concurso::TYPES['online']:
                $title = 'Editar Subasta';
                $description = Concurso::TYPE_DESCRIPTION['online'];
                break;
            case Concurso::TYPES['sobrecerrado']:
                $title = 'Editar Licitación';
                $description = Concurso::TYPE_DESCRIPTION['sobrecerrado'];
                break;
            case Concurso::TYPES['go']:
                $title = 'Edición Concurso Go';
                $description = Concurso::TYPE_DESCRIPTION['go'];
                break;
        }

        return $this->render($response, 'concurso/edit/edit.tpl', [
            'page' => 'concursos',
            'accion' => 'edicion-' . $params['type'],
            'tipo' => $params['type'],
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'concurso_id' => null,
            'isCopy' => 0
        ]);
    }

    public function guardarTokenAcceso(Request $request, Response $response)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $secret = getenv('TOKEN_SECRET_KEY');

        $id = $request->getParsedBody()['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'ID inválido.'
            ], 400);
        }

        $sessionId = session_id();
        $token = hash_hmac('sha256', $id . $sessionId, $secret);

        $_SESSION['edit_token'] = [];
         
        $_SESSION['edit_token'][$id] = $token;

        return $this->json($response, [
            'success' => true
        ]);
    }

    public function serveCreate(Request $request, Response $response, $params)
    {
        $title = null;
        $description = null;
        $concurso_id = $request->getQueryParam('concurso');

        if ($concurso_id) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $secret = getenv('TOKEN_SECRET_KEY');

            $expectedToken = hash_hmac('sha256', $concurso_id . session_id(), $secret);
            $storedToken = $_SESSION['edit_token'][$concurso_id] ?? null;
            if (!$storedToken || $expectedToken !== $storedToken) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Acceso no autorizado. Token inválido para copiar concurso.'
                ], 403);
            }

            //  No se elimina para permitir F5
            // unset($_SESSION['edit_token'][$concurso_id]);
        }

        switch ($params['type']) {
            case Concurso::TYPES['online']:
                $title = 'Nueva Subasta';
                $description = Concurso::TYPE_DESCRIPTION['online'];
                break;
            case Concurso::TYPES['sobrecerrado']:
                $title = 'Nueva Licitación';
                $description = Concurso::TYPE_DESCRIPTION['sobrecerrado'];
                break;
            case Concurso::TYPES['go']:
                $title = 'Nuevo Concurso Go';
                $description = Concurso::TYPE_DESCRIPTION['go'];
                break;
        }

        return $this->render($response, 'concurso/edit/edit.tpl', [
            'page' => 'concursos',
            'accion' => 'nuevo-' . $params['type'],
            'tipo' => $params['type'],
            'id' => 0,
            'title' => $title,
            'description' => $description,
            'concurso_id' => $concurso_id,
            'isCopy' => $concurso_id ? 1 : 0
        ]);
    }


    public function typeList(Request $request, Response $response, $args)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [];

        try {
            $user = user();

            $txt = fopen("user.txt","w");
            fwrite($txt, json_encode($user));

            
            $concursos =
                isAdmin() ?
                Concurso::where([['tipo_concurso', $args['type']]])->get() :
                $user->customer_company->getAllConcursosByCompany()->where('tipo_concurso', $args['type'])->get();
            
            $list = [];
            $etapas = [];
            $concursosOnline = [];
            if ($args['type'] === 'sobrecerrado') {
                $etapasConcursos = DB::table('concursos_x_oferentes')
                    ->whereIn('id_concurso', $concursos->pluck('id'))
                    ->where('etapa_actual', 'economica-revisada')
                    ->get();
            
                $etapas = $etapasConcursos->pluck('etapa_actual', 'id_concurso')->toArray();
            } elseif ($args['type'] === 'online') {
                // Obtener la fecha y hora actual
                $fechaActual = Carbon::now();
            
                // Seleccionar concursos de tipo "online" donde inicio_subasta >= fecha actual
                $concursosOnline = DB::table('concursos')
                    ->where('tipo_concurso', 'online')
                    ->where('inicio_subasta', '>=', $fechaActual)
                    ->select('id')
                    ->distinct()
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }
            
            $list = [];
            foreach ($concursos as $concurso) {
                $etapaActual = $etapas[$concurso->id] ?? null;
            
                // Verificar si el concurso ha comenzado la subasta
                $empezoSubasta = !in_array($concurso->id, $concursosOnline);
                $isSobrecerrado = $args['type'] === 'sobrecerrado';
                $isOnline = $args['type'] === 'online';
                $list[] = [
                    'Id' => $concurso->id,
                    'TipoOperacion' => $concurso->alcance->nombre,
                    'Nombre' => $concurso->nombre,
                    'creadoPor' => $concurso->cliente->first_name . ' ' . $concurso->cliente->last_name,
                    'ImagePath' => filePath(config('app.images_path')),
                    'Portrait' => $concurso->portrait,
                    //'etapaActual' => $etapaActual,
                    //'empezoSubasta' => $empezoSubasta, // Agregar la variable al array
                    //'isSobrecerrado' => $isSobrecerrado, // Bandera para tipo sobrecerrado
                    //'isOnline' => $isOnline,           // Bandera para tipo online
                ];
            }

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Concursos', 'url' => null],
                ['description' => Concurso::TYPE_DESCRIPTION[$args['type']], 'url' => null]
            ];
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs
            ]
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
            ['description' => 'Concursos', 'url' => null],
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
            'ListaConcursosEnPreparacion' => [],
            'ListaConcursosConvocatoriaOferentes' => [],
            'ListaConcursosPropuestasTecnicas' => [],
            'ListaConcursosAnalisisOfertas' => [],
            'ListaConcursosAdjudicados' => [],
            'ListaConcursosEvaluacionReputacion' => [],
            'ListaConcursosInformes' => [],
            'ListaConcursosCancelados' => [],
            
            'DEBUG' => []
        ];


        try {
            
            $user = user();
            // CREATED
            if (isAdmin()) {
                $created = Concurso::all();
            } else if ($user->type_id != 7 && $user->type_id != 3) {
                $created = $user->customer_company->getAllConcursosByCompany()->get();
            } else if ($user->type_id == 3) {
                $created = $user->customer_company->getAllConcursosByCompany()
                    ->where('id_cliente', $user->id)
                    ->get();
            } else {
                $created = Concurso::where([
                    ['ficha_tecnica_usuario_evalua', '=', $user->id]
                ])->get();
            }

            // EVALUATING
            if (isAdmin()) {
                $evaluating = collect();
            } else {
                $evaluating = $user->concursos_evalua;
            }

            // CREATED WITH TRASHED
            if (isAdmin()) {
                $created_with_trashed = Concurso::withTrashed()->get();
            } else if ($user->type_id != 7 && $user->type_id != 3) {
                $created_with_trashed = $user->customer_company->getAllConcursosByCompany()->withTrashed()->get();
            } else if ($user->type_id == 3) {
                $created_with_trashed = $user->customer_company->getAllConcursosByCompany()
                    ->where('id_cliente', $user->id)
                    ->withTrashed()
                    ->get();
            } else {
                $created_with_trashed = Concurso::where([
                    ['ficha_tecnica_usuario_evalua', '=', $user->id],
                    ['deleted_at', '!=', null]
                ])->get();
            }

            // DELETED WITH TRASHED
            if (isAdmin()) {
                $deleted_with_trashed = Concurso::where([
                    ['deleted_at', '!=', null]
                ])->get();
            } else if ($user->type_id != 7 && $user->type_id != 3) {
                $deleted_with_trashed = $user->customer_company->getAllConcursosByCompany()->withTrashed()->get();
            } else if ($user->type_id == 3) {
                $deleted_with_trashed = $user->customer_company->getAllConcursosByCompany()
                    ->where('id_cliente', $user->id)
                    ->withTrashed()
                    ->get();
            } else {
                $deleted_with_trashed = Concurso::where([
                    ['ficha_tecnica_usuario_evalua', '=', $user->id],
                    ['deleted_at', '!=', null]
                ])->get();
            }



            
                //Check if Knockout has passed filters
                if ($filters) {
                    $searchTerm = $filters->searchTerm ?? null;
                    
                    //Checks for a text input to exist
                    if ($searchTerm) {
                        //Checks if search is numeric, (ID search)
                        if (is_numeric($searchTerm)) {

                            //Exact ID match
                            $created = $created->filter(function ($item) use ($searchTerm) {
                                return $item->id == $searchTerm || $item->solicitud_compra == $searchTerm;
                            });
                            
                            $evaluating = $evaluating->filter(function ($item) use ($searchTerm) {
                                return $item->id == $searchTerm || $item->solicitud_compra == $searchTerm;
                            });
                            
                            $created_with_trashed = $created_with_trashed->filter(function ($item) use ($searchTerm) {
                                 return $item->id == $searchTerm || $item->solicitud_compra == $searchTerm;
                             });
                            

                        } else {
                            //Plain text search in name and business_name
                            $created = $created->filter(function ($item) use ($searchTerm) {
                                
                                return 
                                    !!stristr($item->nombre, trim($searchTerm)) ||
                                    !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||
                                    !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                    !!stristr($item->solicitud_compra, trim($searchTerm)) ||
                                    !!stristr($item->area_sol, trim($searchTerm));
                                    
                            });
                            
                            $evaluating = $evaluating->filter(function ($item) use ($searchTerm) {
                                return 
                                    !!stristr($item->nombre, trim($searchTerm)) ||
                                    !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||
                                    !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                    !!stristr($item->solicitud_compra, trim($searchTerm)) ||
                                    !!stristr($item->area_sol, trim($searchTerm));
                            });
                            
                            $created_with_trashed = $created_with_trashed->filter(function ($item) use ($searchTerm) {
                                return 
                                    !!stristr($item->nombre, trim($searchTerm)) ||
                                    !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||  
                                    !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                    !!stristr($item->solicitud_compra, trim($searchTerm)) ||  
                                    !!stristr($item->area_sol, trim($searchTerm));
                            });

                            $deleted_with_trashed = $deleted_with_trashed->filter(function ($item) use ($searchTerm) {
                                return
                                !!stristr($item->nombre, trim($searchTerm)) ||
                                !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||  
                                !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                !!stristr($item->solicitud_compra, trim($searchTerm)) ||  
                                !!stristr($item->area_sol, trim($searchTerm));
                            });
                        }
                    }
                }

            // EN PREPARACI��N
            $concursos = collect();

            $concursos = $concursos->merge(
                $created->filter(function ($concurso) {
                    return $concurso->oferentes_etapa_preparacion->count() > 0;
                })
            )->sortBy('id');
            foreach ($concursos as $concurso) {
                $oferentes = $concurso->oferentes;
                $enable_invitations = $concurso->productos->count() > 0 && $oferentes->where('is_seleccionado', true)->count() > 0;

                array_push(
                    $list['ListaConcursosEnPreparacion'],
                    array_merge(
                        $this->mapConcursoList($concurso),
                        [
                            'HabilitaEnvioInvitaciones' => $enable_invitations
                        ]
                    )
                );
            }

            // CONVOCATORIA OFERENTES
            $concursos = collect($created)
                ->filter(function ($concurso) {
                    // Oferentes que llegaron a la etapa de convocatoria
                    $pendientes = $concurso->oferentes_etapa_convocatoria->count();
                    // Oferentes que ya aceptaron invitación
                    $aceptadas = $concurso->oferentes
                        ->where('has_invitacion_aceptada', true)
                        ->count();

                    // Sólo incluimos si hay pendientes Y cero aceptadas
                    return $pendientes > 0 && $aceptadas === 0;
                })
                ->sortBy('id');
                    
            foreach ($concursos as $concurso) {
                $oferentes = $concurso->oferentes->where('is_seleccionado', false);

                $list['ListaConcursosConvocatoriaOferentes'][] = array_merge(
                    $this->mapConcursoList($concurso),
                    [
                        'CantidadOferentes'      => $oferentes->count(),
                        'CantidadPresentaciones' => $oferentes
                            ->where('has_invitacion_aceptada', true)
                            ->count(),
                    ]
                );
            }

            // PROPUESTAS T��CNICAS
            $etapas_tecnica = array_merge(
                [
                    'tecnica-pendiente', 'tecnica-pendiente-2', 'tecnica-pendiente-3', 'tecnica-pendiente-4', 'tecnica-pendiente-5',
                    'tecnica-presentada', 'tecnica-presentada-2', 'tecnica-presentada-3', 'tecnica-presentada-4', 'tecnica-presentada-5',
                    'tecnica-declinada', 'tecnica-declinada-2','tecnica-declinada-3', 'tecnica-declinada-4','tecnica-declinada-5',
                    'tecnica-rechazada', 'tecnica-rechazada-2', 'tecnica-rechazada-3', 'tecnica-rechazada-4', 'tecnica-rechazada-5'
                ]
            );

            $etapas_economica = [
                'economica-pendiente', 'economica-presentada',
                'economica-declinada', 'economica-rechazada',
                'adjudicacion-aceptada', 'adjudicacion-rechazada'
              ];

            $concursos = collect();
            $concursos = $concursos
                ->merge(
                    $created
                    ->filter(function ($concurso) use ($etapas_tecnica) {
                        return $concurso->ficha_tecnica_incluye === "si" &&
                            $concurso->oferentes->whereIn('etapa_actual', $etapas_tecnica)->count() > 0;
                    })

                )
                ->merge(
                    $evaluating
                        ->where('technical_includes', true)
                        ->filter(function ($concurso) use ($etapas_tecnica) {
                            return $concurso->ficha_tecnica_incluye === "si" &&
                                $concurso->oferentes->whereIn('etapa_actual', $etapas_tecnica)->count() > 0;
                        })
                )
                ->unique('id')
                ->filter(function($concurso) use ($etapas_economica) {
                    // si algún oferente ya está en etapa económica, lo excluimos;
                    // si no, lo incluimos, sin importar la fecha técnica.
                    return $concurso->oferentes
                            ->whereIn('etapa_actual', $etapas_economica)
                            ->isEmpty();
                })
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                $fechaTecnica = $concurso->ficha_tecnica_fecha_limite->format('d-m-Y');
                $horaTecnica = $concurso->ficha_tecnica_fecha_limite->format('H:i:s');
                array_push(
                    $list['ListaConcursosPropuestasTecnicas'],
                    array_merge(
                        $this->mapConcursoList($concurso),
                        [
                            'CantidadOferentes' => $concurso->oferentes
                                ->where('is_seleccionado', false)
                                ->where('has_invitacion_aceptada', true)
                                ->count(),
                            'CantidadPresentaciones' => $concurso->oferentes
                                ->where('has_tecnica_presentada', true)
                                ->count(),
                            'FechaTecnica' => $fechaTecnica,
                            'HoraTecnica' => $horaTecnica,
                        ]   
                    )
                );
            }

            // PROPUESTAS ECONÓMICAS
            $concursos = collect();
            $concursos = $concursos->merge(
                $created
                    ->filter(function ($concurso) {
                        return $concurso->oferentes_etapa_economica->count() > 0;
                    })
            )->sortBy('id');

            foreach ($concursos as $concurso) {
                $oferentes = $concurso->oferentes_etapa_economica;

                // 1) Determino el objeto DateTime que usaré (puede ser null)
                if ($concurso->is_sobrecerrado || $concurso->is_go) {
                    $dt = $concurso->fecha_limite_economicas;
                } elseif ($concurso->is_online) {
                    $dt = $concurso->inicio_subasta;
                } else {
                    $dt = null;
                }

                // 2) Formateo fecha/hora solo si existe
                if ($dt instanceof \DateTimeInterface) {
                    $fecha              = $dt->format('d-m-Y');
                    $hora               = $dt->format('H:i:s');
                    $fechaEconomicaOrden = $dt->format('Y-m-d H:i:s');
                } else {
                    $fecha               = '';
                    $hora                = '';
                    $fechaEconomicaOrden = '';
                }

                // 3) Calculo estado y cantidad según reglas
                if ($concurso->is_sobrecerrado || $concurso->is_go) {
                    if ($dt instanceof \DateTimeInterface && $concurso->fecha_limite_economicas > Carbon::now()) {
                        $status_text = 'Licitando';
                    } elseif ($concurso->adjudicacion_anticipada
                            && $oferentes->where('has_economica_presentada', false)->count() > 0) {
                        $status_text = 'Fin parcial';
                    } else {
                        $status_text = 'Finalizado';
                    }
                    $cantidad = $concurso->oferentes
                        ->where('has_economica_presentada', true)
                        ->count();
                } elseif ($concurso->is_online) {
                    if ($concurso->timeleft) {
                        $status_text = 'No iniciado';
                        $cantidad    = 0;
                    } elseif ($concurso->countdown) {
                        $status_text = 'Licitando';
                        $cantidad    = 0;
                    } else {
                        $status_text = 'Finalizado';
                        $cantidad    = $concurso->oferentes
                            ->where('has_economica_presentada', true)
                            ->count();
                    }
                } else {
                    $status_text = '';
                    $cantidad    = 0;
                }

                $oferentesActivos = $concurso->oferentes->filter(function($oferente) {
                    return !$oferente->is_concurso_rechazado;
                });

                $cantidadPresentaciones = $oferentesActivos
                    ->where('has_economica_presentada', true)
                    ->count();

                // 4) Inserto al listado incluyendo el nuevo campo FechaEconomicaOrden
                array_push(
                    $list['ListaConcursosAnalisisOfertas'],
                    array_merge(
                        $this->mapConcursoList($concurso),
                        [
                            'CantidadOferentes'      => $oferentesActivos->count(),
                            'CantidadPresentaciones' => $cantidadPresentaciones,
                            'Fecha'                  => $fecha,
                            'Hora'                   => $hora,
                            'FechaEconomicaOrden'    => $fechaEconomicaOrden,
                            'Estado'                 => $status_text
                        ]
                    )
                );
            }


            // EVALUACI��N
            $concursos = collect();
            $concursos = $concursos
                ->merge(
                    $created
                        ->where('adjudicado', true)
                )
                ->merge(
                    $evaluating
                        ->where('adjudicado', true)
                )
                ->merge(
                    $evaluating
                        ->where('adjudicado', true)
                )
                ->unique('id')
                ->filter(function ($concurso) {
                    return $concurso->oferentes_etapa_evaluacion->count() > 0;
                })
                /*->filter(function ($concurso) {
                    return $concursos = $concurso->oferentes
                    ->where('etapa_actual', Participante::ETAPAS['estrategia-aceptada'])
                    ->all();
                })*/
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                array_push(
                    $list['ListaConcursosEvaluacionReputacion'],
                    $this->mapConcursoList($concurso)
                );
            }


            // INFORMES
                $concursos = collect();
                $concursos = $concursos->merge(
                    $created->filter(function ($concurso) {
                        // Filtrar oferentes con adjudicación aceptada
                        $oferentesAdjudicados = $concurso->oferentes
                            ->where('etapa_actual', 'adjudicacion-aceptada');

                        // Para cada oferente adjudicado, buscamos si tiene evaluación asociada
                        foreach ($oferentesAdjudicados as $oferente) {
                            $evaluado = Evaluacion::where('id_participante', $oferente->id)->exists();
                            if ($evaluado) {
                                return true;
                            }
                        }

                        return false;
                    })
                );

                foreach ($concursos as $concurso) {
                    array_push(
                        $list['ListaConcursosInformes'],
                        $this->mapConcursoList($concurso)
                    );
                }


            // CANCELADOS
                $concursos = collect();
               if ($user->type_id == 7) {
                    $concursos = Concurso::where([
                        ['deleted_at', '!=', null],
                        ['ficha_tecnica_usuario_evalua', '=', $user->id]
                    ])->get();
                } else if ($user->type_id == 3) {
                    $concursos = $user->customer_company->getAllConcursosByCompany()
                        ->where('id_cliente', $user->id)
                        ->onlyTrashed()
                        ->get();
                } else {
                    $concursos = $user->customer_company->getAllConcursosByCompany()
                        ->onlyTrashed()
                        ->get();
                }

                // Aplico los mismos filtros que al principio
                if ($filters) {
                    $searchTerm = $filters->searchTerm ?? null;

                    if ($searchTerm) {
                        if (is_numeric($searchTerm)) {
                            $concursos = $concursos->filter(function ($item) use ($searchTerm) {
                                return $item->id == $searchTerm;
                            });
                        } else {
                            $concursos = $concursos->filter(function ($item) use ($searchTerm) {
                                return 
                                    !!stristr($item->nombre, trim($searchTerm)) ||
                                    !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||
                                    !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                    !!stristr($item->area_sol, trim($searchTerm));
                            });
                        }
                    }
                }

                $concursos = $concursos->sortBy('id');

                foreach ($concursos as $concurso) {
                    array_push(
                        $list['ListaConcursosCancelados'],
                        array_merge(
                            $this->mapConcursoList($concurso),
                            [
                                'FechaCancelacion' => $concurso->deleted_at->format('d-m-Y'),
                                'HoraCancelacion' => $concurso->deleted_at->format('H:i:s'),
                                'FechaCancelacionOrden' => $concurso->deleted_at->format('Y-m-d H:i:s'),
                                'Estado' => 'Cancelado'
                            ]
                        )
                    );
                }


            $success = true;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }
        $userType = UserType::find((int) $user->type_id);


        
        return [
            'success' => $success,
            'message' => $message,
            'list' => $list,
            'userType' => $userType->code,
            'status' => $status
            
        ];
    }


    public function delete(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];
        $redirect_url = null;

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = $request->getParsedBody();
            $user = user();
            $concurso = $user->customer_company->getAllConcursosByCompany()->find($params['id']);

            $validator = validator(
                $data = [
                    'reason' => $body['Reason']
                ],
                $rules = [
                    'reason' => 'required|string'
                ],
                $messages = [
                    'reason.required' => 'Debe ingresar un motivo para cancelar el concurso.'
                ]
            );

            if ($validator->fails()) {
                $success = false;
                $status = 422;
                $message = $validator->errors()->first();
            } else {
                $concurso->update([
                    'comentario_cancelacion' => $body['Reason'],
                    'usuario_cancelacion' => $user->id
                ]);

                $concurso->delete();

                // Marcar como rechazados a los oferentes
                $companiesInvited = $concurso->oferentes->pluck('id_offerer');
                $companies = OffererCompany::with('users')->whereIn('id', $companiesInvited)->get();
                $offerersTable = (new Participante())->getTable();
                DB::table($offerersTable)
                    ->where('id_concurso', $concurso->id)
                    ->whereIn('id_offerer', $companiesInvited)
                    ->update(['rechazado' => true]);

                // Enviar mails a los oferentes
                $title = 'Concurso Cancelado';
                $subject = $concurso->nombre . ' - ' . $title;
                $templateOferentes = rootPath(config('app.templates_path')) . '/email/cancellation.tpl';

                foreach ($companies as $company) {
                    $users = $company->users->pluck('email');
                    $html = $this->fetch($templateOferentes, [
                        'title' => $title,
                        'ano' => Carbon::now()->format('Y'),
                        'concurso' => $concurso,
                        'company_name' => $company->business_name
                    ]);

                    $result = $emailService->send($html, $subject, $users, "");
                    $success = $result['success'];
                    if (!$success) break;
                }

                // Enviar mail al usuario que canceló el concurso
                if ($success) {
                    $templateUsuario = rootPath(config('app.templates_path')) . '/email/delete-confirmation-client.tpl';

                    $htmlUser = $this->fetch($templateUsuario, [
                        'user' => $user,
                        'concurso' => $concurso
                    ]);

                    $result = $emailService->send($htmlUser, 'Confirmación de Cancelación de Concurso', [$user->email], "");
                    $success = $result['success'];
                }

                if ($success) {
                    $connection->commit();
                    $message = 'Concurso cancelado con éxito.';
                    $redirect_url = route('concursos.cliente.serveList');
                } else {
                    $connection->rollBack();
                    $message = $message ?? 'Han ocurrido errores al enviar las notificaciones.';
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
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }

    private function mapConcursoList($concurso)
    {
        // $file = fopen("messi.txt", "w");
        // fwrite($file, json_encode($concurso->getAttributes()));
        // fclose($file);
        return [
            'Id' => $concurso->id,
            'Nombre' => $concurso->nombre,
            'Solicitante' => $concurso->cliente->customer_company->business_name,
            'AreaSolicitante' => $concurso->area_sol, //Agregado por bauti 
            'UsuarioSolicitante' => $concurso->cliente->full_name, //Agregado por valen 
            'NumSolicitud' => $concurso->solicitud_compra, //Agregado por el fede
            'FechaLimite' => $concurso->fecha_limite->format('d-m-Y H:i'),
            'FechaLimiteOrden' => $concurso->fecha_limite->format('Y-m-d H:i:s'), //Agregado por benja
            'TipoConcurso' => $concurso->tipo_concurso_nombre,
            'TipoConcursoPath' => strtolower(trim($concurso->tipo_concurso))
        ];
    }

    private function getFechasPropuestaTecnicaPorRonda($concurso)
    {
        $fechas = [];
        foreach ($concurso->oferentes as $oferente) {
            $fechas[$oferente->id] = [
                1 => $oferente->fecha_primera_ronda_tecnica
                    ? $oferente->fecha_primera_ronda_tecnica->format('d-m-Y H:i')
                    : null,
                2 => $oferente->fecha_segunda_ronda_tecnica
                    ? $oferente->fecha_segunda_ronda_tecnica->format('d-m-Y H:i')
                    : null,
                3 => $oferente->fecha_tercera_ronda_tecnica
                    ? $oferente->fecha_tercera_ronda_tecnica->format('d-m-Y H:i')
                    : null,
                4 => $oferente->fecha_cuarta_ronda_tecnica
                    ? $oferente->fecha_cuarta_ronda_tecnica->format('d-m-Y H:i')
                    : null,
                5 => $oferente->fecha_quinta_ronda_tecnica
                    ? $oferente->fecha_quinta_ronda_tecnica->format('d-m-Y H:i')
                    : null,
            ];
        }
        return $fechas;
    }



    public function detail(Request $request, Response $response, $params)
    {
        date_default_timezone_set(user()->customer_company->timeZone);

        $success = false;
        $message = null;
        $status = 200;
        $list = [
            'RondasOfertas' => [],
            'ConcursoEconomicasPrimeraRonda' => [],
            'ConcursoEconomicasSegundaRonda' => [],
            'HabilitaSegundaRonda' => 'no'
        ];
        $results = [];

        try {
            $user = user();

            if (isAdmin()) {
                $concurso = Concurso::find($params['id']);
            } else {
                $concurso = $user->customer_company->getAllConcursosByCompany()->find($params['id']);
                $concurso = $concurso ?? $user->concursos_evalua->find($params['id']);
            }
            $list['HabilitaSegundaRonda'] = $concurso->segunda_ronda_habilita;
            $terminos_filename = rootPath(config('app.templates_path')) . '/terminos-cliente.tpl';

            if (is_file($terminos_filename)) {
                ob_start();
                include $terminos_filename;
                $terminos = ob_get_clean();
            }

            $filePath = config('app.files_path') . $user->customer_company->cuit . '/' . substr($concurso->fecha_limite, 0, 4) . '/';
            $fechaActual = date('d-m-Y H:i:s');

            $fechaMinimaSegundaRonda = date('d-m-Y H:i', strtotime($fechaActual . ' + 3 days'));
            $fechaMaximaMuroConsulta = date('d-m-Y H:i', strtotime($fechaMinimaSegundaRonda . ' - 2 days'));
            $plazoVencidoEconomicas = false;


            if ($concurso->segunda_ronda_habilita == 'si') {
                $field = Concurso::CAMPOS_FECHA_NUEVA_RONDA[$concurso->ronda_actual];
                $plazoVencidoEconomicas = $concurso->$field < Carbon::now();
            } else {
                $plazoVencidoEconomicas = $concurso->fecha_limite_economicas < Carbon::now();
            }
            $fechasNuevasRondas = [];
            if ($concurso->economic_includes_second_round) {

            }
            $route_name = 'concursos.cliente.serveDetail';
            
            $common_data = [
                'IdConcurso' => $concurso->id,
                'Tipo' => $concurso->tipo_concurso,
                'IsGo' => $concurso->is_go,
                'IsOnline' => $concurso->is_online,
                'IsSubastaciega' => $concurso->is_subastaciega,
                'IsSobrecerrado' => $concurso->is_sobrecerrado,
                'DisponibleHabilitarSegundaRondaEconomica' => $concurso->disponible_habilitar_segunda_ronda_economica,
                'ImagePath' => filePath(config('app.images_path'), true),
                'FilePath' => filePath($filePath, true),
                'Adjudicado' => $concurso->adjudicado,
                'Eliminado' => $concurso->trashed(),
                'IncluyeTecnica' => $concurso->technical_includes,
                'AceptacionInvitacion' => $concurso->fecha_limite->format('d-m-Y'),
                'AceptacionInvitacionHora' => $concurso->fecha_limite->format('H:i:s'),
                'CierreMuroConsultas' => $concurso->finalizacion_consultas->format('d-m-Y'),
                'CierreMuroConsultasHora' => $concurso->finalizacion_consultas->format('H:i:s'),
                'PlazoVencidoEconomica' => $plazoVencidoEconomicas,
                'PresentacionTecnicas' =>
                    $concurso->ficha_tecnica_fecha_limite ?
                    $concurso->ficha_tecnica_fecha_limite->format('d-m-Y') :
                    null,
                'PresentacionTecnicasHora' =>
                    $concurso->ficha_tecnica_fecha_limite ?
                    $concurso->ficha_tecnica_fecha_limite->format('H:i:s') :
                    null,
                'PresentacionEconomicas' =>
                    $concurso->fecha_limite_economicas ?
                    $concurso->fecha_limite_economicas->format('d-m-Y') :
                    null,
                'PresentacionEconomicasHora' =>
                    $concurso->fecha_limite_economicas ?
                    $concurso->fecha_limite_economicas->format('H:i:s') :
                    null,
                ///
                'PresentacionEconomicasSegundaRondaMinimo' => $concurso->fecha_limite_economicas 
                    ? $fechaMinimaSegundaRonda 
                    : null,
                ///
                'IncluyeEconomicaSegundaRonda' => $concurso->economic_includes_second_round,
                'PresentacionEconomicasSegundaRonda' =>
                    $concurso->segunda_ronda_fecha_limite ?
                    $concurso->segunda_ronda_fecha_limite->format('d-m-Y') :
                    null,
                'PresentacionEconomicasSegundaRondaHora' =>
                    $concurso->segunda_ronda_fecha_limite ?
                    $concurso->segunda_ronda_fecha_limite->format('H:i:s') :
                    null,
                    
                'IncluyeEconomicaTerceraRonda' => $concurso->tercera_ronda_fecha_limite !== null,
                'PresentacionEconomicasTerceraRonda' =>
                    $concurso->tercera_ronda_fecha_limite ?
                    $concurso->tercera_ronda_fecha_limite->format('d-m-Y') :
                    null,   
                'PresentacionEconomicasTerceraRondaHora' =>
                    $concurso->tercera_ronda_fecha_limite ?
                    $concurso->tercera_ronda_fecha_limite->format('H:i:s') :
                    null,

                'IncluyeEconomicaCuartaRonda' => $concurso->cuarta_ronda_fecha_limite !== null,
                'PresentacionEconomicasCuartaRonda' =>
                    $concurso->cuarta_ronda_fecha_limite ?
                    $concurso->cuarta_ronda_fecha_limite->format('d-m-Y') :
                    null,
                'PresentacionEconomicasCuartaRondaHora' =>
                    $concurso->cuarta_ronda_fecha_limite ?
                    $concurso->cuarta_ronda_fecha_limite->format('H:i:s') :
                    null,
                
                'IncluyeEconomicaQuintaRonda' => $concurso->quita_ronda_fecha_limite !== null,
                'PresentacionEconomicasQuintaRonda' =>
                    $concurso->quita_ronda_fecha_limite ?
                    $concurso->quita_ronda_fecha_limite->format('d-m-Y') :
                    null,
                'PresentacionEconomicasQuintaRondaHora' =>
                    $concurso->quita_ronda_fecha_limite ?
                    $concurso->quita_ronda_fecha_limite->format('H:i:s') :
                    null,

                'InicioSubasta' =>
                    $concurso->inicio_subasta ?
                    $concurso->inicio_subasta->format('d-m-Y') :
                    null,
                'InicioSubastaHora' =>
                    $concurso->inicio_subasta ?
                    $concurso->inicio_subasta->format('H:i:s') :
                    null,
                'Nombre' => $concurso->nombre,
                'Solicitante' => $concurso->cliente->customer_company->business_name,
                'Administrador' => ucwords(strtolower($concurso->cliente->full_name)),
                'Tipologia' => $concurso->tipo_concurso_nombre,
                'TipoOperacion' => $concurso->alcance->nombre,
                'Portrait' => $concurso->portrait,
                'TerminosCondiciones' => $terminos,
                'ZonaHoraria' => user()->customer_company->country . '/' . $this->toGmtOffset(user()->customer_company->timeZone),
                'AdjudicacionItems' => $concurso->adjudicacion_items ? $concurso->adjudicacion_items : [],
                'AdjudicacionComentario' => $concurso->adjudicacion_comentario,
                'FechaCancelacion' => $concurso->trashed() ? $concurso->deleted_at->format('d-m-Y H:i:s') : null,
                'UsuarioCancelacion' => $concurso->cancelacion_usuario ? $concurso->cancelacion_usuario->full_name : null,
                'Productos' => $concurso->productos,
                'urlChatMuro' => route($route_name, ['type' => $concurso->tipo_concurso, 'id' => $concurso->id, 'step' => 'chat-muro-consultas']),
                'concurso_fiscalizado' => $concurso->concurso_fiscalizado,
                'ChatEnable' => $concurso->is_sobrecerrado ? true : ($concurso->chat == 'si' ? true : false ),
                'emailSuper' => $concurso->concurso_fiscalizado == 'si' ? $concurso->supervisor->email : null    

            ];

            $companies = OffererCompany::whereHas('associated_customers', function ($query) use ($user) {
                $query->where('customer_id', $user->customer_company->id);
            })->whereHas('users')->get();

            foreach ($companies as $company) {
                $text = strtoupper($company->business_name);
                $text = $company->cuit ? $text . ', ' . $company->cuit : $text;
                $results[] = [
                    'id' => $company->id,
                    'text' => $text
                ];
            }
            $oferentesInvitados = $this->getSeguimientoInvitaciones($concurso);


            foreach ($oferentesInvitados as $oferente) {
                $clave = array_search($oferente["IdOferente"], array_column($results, 'id'));
                if (($clave = array_search($oferente["IdOferente"], array_column($results, 'id'))) !== false) {
                    unset($results[$clave]);
                    $results = array_values($results);
                }
            }


            // CONVOCATORIA OFERENTES
            if ($params['step'] === Step::STEPS['customer']['convocatoria-oferentes']) {

                $user = user();
                $user_type_id = $user->type_id;
                $user_type = UserType::where('id', (int) $user_type_id)->value('code');
                
                $file_path = filePath($concurso->file_path, true);
                $media = [];
                foreach ($concurso->sheets as $sheet) {
                    $media[] = [
                        'indice' => $sheet->type->id,
                        'nombre' => $sheet->type->description,
                        'imagen' => $sheet->filename,
                        'path' => filePath($file_path . $sheet->filename)
                    ];
                }
                $products = [];
                foreach ($concurso->productos as $product) {
                    $products[] = [
                        'product_id'          => $product->id,
                        'product_name'        => $product->nombre,
                        'product_description' => $product->descripcion,
                        'currency_id'         => $concurso->tipo_moneda->id,
                        'currency_name'       => $concurso->tipo_moneda->nombre,
                        'minimum_quantity'    => $product->oferta_minima,
                        'total_quantity'      => $product->cantidad,
                        'measurement_id'      => $product->unidad_medida->id,
                        'measurement_name'    => $product->unidad_medida->name,
                    ];
                }

                // Obtener el registro de User
                $evalUser = User::find($concurso->ficha_tecnica_usuario_evalua);

                // Extraer first + last intentando varios campos
                if ($evalUser) {
                    // Si tu DB realmente usa first_name / last_name:
                    $first = $evalUser->first_name  ?? null;
                    $last  = $evalUser->last_name   ?? null;

                    // Si esos no existen, probar name o username
                    if (! $first && isset($evalUser->name)) {
                        // asumimos que name es “Nombre Apellido”
                        [$first, $last] = array_pad(explode(' ', $evalUser->name, 2), 2, '');
                    }
                    if (! $first && isset($evalUser->username)) {
                        $first = $evalUser->username;
                    }

                    $evalName = trim($first . ' ' . $last);
                    if ($evalName === '') {
                        $evalName = '— Sin evaluador —';
                    }
                } else {
                    $evalName = '— Sin evaluador —';
                }

                $list = array_merge($list, array_merge($common_data, [
                    'Media' => $media,
                    'Productos' => $products,
                    'OferentesInvitados' => $oferentesInvitados,
                    'OferentesAInvitar' => $results,
                    'OferenteAInvitar' => null,
                    'Evaluador' => $evalName,
                    //'Usertype' => $user_type
                ]));
            }

            // CHAT
            if ($params['step'] === Step::STEPS['customer']['chat-muro-consultas']) {
                $list = array_merge($list, $common_data);
            }

            // ANÁLISIS TÉCNICAS
            if ($params['step'] === Step::STEPS['customer']['analisis-tecnicas']) {
                // 1) Obtengo las evaluaciones tal cual
                $techEvals = $concurso->technical_includes
                    ? $this->getTechnicalEvaluations($concurso)
                    : [];

                // 2) Obtengo todas las fechas por oferente y ronda
                $fechas = $this->getFechasPropuestaTecnicaPorRonda($concurso);

                // 3) Inyecto en cada ronda su fecha correspondiente
                foreach ($techEvals as &$eval) {
                    // ojo con la clave: si en tu getTechnicalEvaluations usas 'OferenteId' o 'oferente_id'
                    $oferenteId = $eval['OferenteId']; 
                    foreach ($eval['rondasTecnicas'] as $idx => &$ronda) {
                        $roundNum = $idx + 1; // 0→1, 1→2, etc.
                        $ronda['fecha_envio_propuesta'] = 
                            $fechas[$oferenteId][$roundNum] ?? null;
                    }
                    unset($ronda);
                }
                unset($eval);

                // 4) Hago el merge FINAL con la propiedad ya inyectada
                $list = array_merge($list, $common_data, [
                    'OferentesInvitados' => $oferentesInvitados,
                    'TechnicalEvaluations' => $techEvals,
                    'TechnicalProposals'    => $concurso->parsed_technical_proposals,
                ]);
            }

            // ANÁLISIS ECONÓMICAS
            if ($params['step'] === Step::STEPS['customer']['analisis-ofertas']) {
                require rootPath() . '/app/OldServices/calculos-etapas.php';
                calcularEtapaAnalisisOfertas($list, $concurso->id);

                foreach ($companies as $company) {
                    $text = strtoupper($company->business_name);
                    $text = $company->cuit ? $text . ', ' . $company->cuit : $text;
                    $oferentesSegundaRonda[] = [
                        'id' => $company->id,
                        'text' => $text
                    ];
                }

                $tipo_adjudicacion = null;
                $adjudicacion_manual = [];
                if ($concurso->oferentes->whereIn('adjudicacion', [1, 2, 3])->count() > 0) {
                    $id_adjudicacion = $concurso->oferentes
                        ->whereIn('adjudicacion', [1, 2, 3])
                        ->first()->adjudicacion;

                    switch ($id_adjudicacion) {
                        case 1:
                            $tipo_adjudicacion = 'integral';
                            break;
                        case 2:
                            $tipo_adjudicacion = 'individual';
                            break;
                        case 3:
                            $tipo_adjudicacion = 'manual';
                            $adjudicacion_manual = $concurso->adjudicacion_items;
                            break;
                    }
                }

                $adjudicacion_manual_items = [];
                foreach ($adjudicacion_manual as $item) {
                    $adjudicacion_manual_items[] = [
                        'product_id' => (string) $item['itemId'],
                        'offerer_id' => (string) $item['oferenteId'],
                        'quantity' => $item['cantidad'],
                        'cantidadAdj' => $item['cantidadAdj']
                    ];
                }

                $oferentes = $concurso->getOferentesData();
                $userType = UserType::find((int) $user->type_id);
                $verOfertasEnable = false;
                $ejecutarNuevaRonda = false;

                if($concurso->is_sobrecerrado){
                    $ejecutarNuevaRonda = ($concurso->ronda_actual < Concurso::MAX_RONDAS) ? true : false;
                }
                
                
                if (!$concurso->adjudicado && $concurso->adjudicacion_anticipada && ($concurso->alguno_presento_economica || $plazoVencidoEconomicas)) {
                    $verOfertasEnable = true;
                }

                if (!$concurso->adjudicado && !$concurso->adjudicacion_anticipada && ($concurso->todos_presentaron_economica || $plazoVencidoEconomicas)) {
                    $verOfertasEnable = true;
                }

                if ($concurso->technical_includes) {
                    $proveedores = $concurso->oferentes->where('has_tecnica_aprobada');
                } else {
                    $proveedores = $concurso->oferentes->where('has_invitacion_aceptada');
                }

                $proveedoresInvitados = [];
                foreach ($proveedores as $proveedor) {
                    // buscas la última propuesta registrada para este participante
                    $proposal = Proposal::where('participante_id', $proveedor->id)
                                        ->orderBy('updated_at', 'desc')
                                        ->first();


                    $proveedoresInvitados[] = [
                        'razonSocial'       => $proveedor->company->business_name,
                        'participa'         => $proveedor->etapa_actual !== 'economica-declinada',
                        'presento'          => $proveedor->has_economica_presentada,
                        // si hay proposal, formateas la fecha; si no, pasas null
                        'fechaPresentacion' => $proposal
                            ? $proposal->updated_at->format('d-m-Y H:i')
                            : null,
                    ];
                }

                $list = array_merge($list, array_merge($common_data, [
                    'OferentesInvitados' => $oferentesInvitados,
                    'Oferentes' => $oferentes->toArray(),
                    'TipoValorOferta' => $concurso->tipo_valor_ofertar,
                    'CantidadOferentes' => $concurso->oferentes
                        ->whereIn('etapa_actual', Participante::ETAPAS_ECONOMICAS)->count(),
                    'TodosPresentaronEconomica' => $concurso->todos_presentaron_economica,
                    'IsRevisado' => $concurso->adjudicacion_anticipada ? $concurso->alguno_revisado : $concurso->todos_revisados,
                    'TipoAdjudicacion' => $tipo_adjudicacion,
                    'AdjudicacionAnticipada' => $concurso->adjudicacion_anticipada,
                    'ExistenOfertas' => $concurso->existen_ofertas,
                    'AlgunoPresentoEconomica' => $concurso->alguno_presento_economica,
                    'Duracion' =>
                        isset($concurso->parsed_duracion) ?
                        $concurso->parsed_duracion[0] . ' minutos ' . $concurso->parsed_duracion[1] . ' segundos' :
                        ' 0 minutos 0 segundos',
                    'TiempoAdicional' => $concurso->tiempo_adicional,
                    'Countdown' => $concurso->countdown,
                    'Timeleft' => $concurso->timeleft,
                    'UnidadMinima' => $concurso->unidad_minima,
                    'SoloOfertasMejores' => $concurso->solo_ofertas_mejores,
                    'PrecioMaximo' => $concurso->precio_maximo,
                    'PrecioMinimo' => $concurso->precio_minimo,
                    'Chat' => $concurso->chat,
                    'VerNumOferentesParticipan' => $concurso->ver_num_oferentes_participan,
                    'Moneda' => $concurso->tipo_moneda->nombre,
                    'ManualAdjudicationProductList' => $concurso->productos->map(
                        function ($item) {
                            return [
                                'id' => (string) $item->id,
                                'text' => $item->nombre
                            ];
                        }
                    ),
                    'ManualAdjudicationItems' => $adjudicacion_manual_items,
                    'OferentesPrimeraRonda' => $oferentesInvitados,
                    'UserType' => $userType->code,
                    'rondaActual' => $concurso->ronda_actual - 1,
                    'rondaTitle' => $concurso->ronda_actual,
                    'maxRonda' => Concurso::MAX_RONDAS,
                    'nuevaRonda' => $concurso->ronda_actual == Concurso::MAX_RONDAS ? '' : Concurso::NUEVAS_RONDAS[$concurso->ronda_actual + 1],
                    'verOfertasEnable' => $verOfertasEnable,
                    'EjecutarNuevaRonda' => $ejecutarNuevaRonda,
                    'Proveedores' => $proveedoresInvitados,
                ]));

                if ($concurso->is_online) {
                    $list = array_merge(
                        $list,
                        $concurso->getSubastaOutput()
                    );
                }
            }

            // EVALUACIÓN REPUTACIÓN
            if ($params['step'] === Step::STEPS['customer']['evaluacion-reputacion']) {
                require rootPath() . '/app/OldServices/calculos-etapas.php';
                calcularEtapaAnalisisOfertas($list, $concurso->id);

                $tipo_adjudicacion = null;
                if ($concurso->oferentes->whereIn('adjudicacion', [1, 2, 3])->count() > 0) {
                    $id_adjudicacion = $concurso->oferentes
                        ->whereIn('adjudicacion', [1, 2, 3])
                        ->first()->adjudicacion;
                    switch ($id_adjudicacion) {
                        case 1:
                            $tipo_adjudicacion = 'Adjudicación Integral';
                            break;
                        case 2:
                            $tipo_adjudicacion = 'Adjudicación Individual';
                            break;
                        case 3:
                            $tipo_adjudicacion = 'Adjudicación Manual';
                            break;
                    }
                }
                $verOfertasEnable = false;
                $ejecutarNuevaRonda = $concurso->ronda_actual === Concurso::MAX_RONDAS ? false : true;
                if (!$concurso->adjudicado && $concurso->adjudicacion_anticipada && ($concurso->alguno_presento_economica || $plazoVencidoEconomicas)) {
                    $verOfertasEnable = true;
                }
               
                if (!$concurso->adjudicado && !$concurso->adjudicacion_anticipada && ($concurso->todos_presentaron_economica || $plazoVencidoEconomicas)) {
                    $verOfertasEnable = true;
                }



                $list = array_merge($list, array_merge($common_data, [
                    'OferentesInvitados' => $oferentesInvitados,
                    'titleEvaluaciones' => 'Evaluación de la ' . $concurso->tipo_concurso_nombre,
                    'titleResultados' => 'Resultados de la ' . $concurso->tipo_concurso_nombre,
                    'Evaluaciones' => $this->getOfferersReputation($concurso),
                    'AdjudicacionAnticipada' => $concurso->adjudicacion_anticipada,
                    'TipoAdjudicacion' => $tipo_adjudicacion,
                    'rondaActual' => $concurso->ronda_actual - 1,
                    'rondaTitle' => $concurso->ronda_actual,
                    'maxRonda' => Concurso::MAX_RONDAS,
                    'nuevaRonda' => $concurso->ronda_actual == Concurso::MAX_RONDAS ? '' : Concurso::NUEVAS_RONDAS[$concurso->ronda_actual + 1],
                    'verOfertasEnable' => $verOfertasEnable,
                    'EjecutarNuevaRonda' => $ejecutarNuevaRonda,
                ]));
            }

            // INFORMES
            if ($params['step'] === Step::STEPS['customer']['informes']) {
                $list = array_merge($list, $common_data);
            }
            $success = true;

            $breadcrumbs = [
                ['description' => 'Concursos', 'url' => null],
                ['description' => 'Monitor', 'url' => '/concursos/cliente'],
                ['description' => getStepName($params['step'], $concurso->is_go), 'url' => null]
            ];
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs,
                'steps' => Step::getByConcurso($concurso, $params['step'])
            ]
        ], $status);
    }

        // crear el formulario para crear o editar un concurso
    public function editOrCreate(Request $request, Response $response, $params)
    {

        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        date_default_timezone_set(user()->customer_company->timeZone);
        $create = (bool) ($params['action'] === 'create');
        $is_copy = isset($params['id']);
        $isReadOnly = false;
        $emailService = new EmailService();
        $bloquearInvitacionOferentes = false;


        try {

            $user = user();

            if ($create) {
                $is_go = $params['type'] == Concurso::TYPES['go'];
                $is_sobrecerrado = $params['type'] == Concurso::TYPES['sobrecerrado'];
                $is_online = $params['type'] == Concurso::TYPES['online'];
                // Borramos los productos temporales que existan.
                $productsTable = (new Producto())->getTable();
                DB::table($productsTable)->where('id_concurso', 0)->where('id_usuario', $user->id)->delete();
                $habilita_envio_invitaciones = false;
                $concurso = $is_copy ? user()->customer_company->getAllConcursosByCompany()->find($params['id']) : null;
            } else {
                // Obtenemos el Concurso y los datos complementarios
                $concurso = user()->customer_company->getAllConcursosByCompany()->find($params['id']);
                $is_go = $concurso->is_go;
                $is_sobrecerrado = $concurso->is_sobrecerrado;
                $is_online = $concurso->is_online;
                $fechaActual = Carbon::now();
                $fechasubasta = $concurso->inicio_subasta;
                
                $habilita_envio_invitaciones = $concurso->productos->count() > 0 && $concurso->oferentes->where('is_seleccionado', true)->count() > 0;

                $is_revisado = $concurso->oferentes->where('has_economica_revisada', true)->count() > 0 ? true : false;
                $invitacionesExistentes = DB::table('invitations')->where('concurso_id', $concurso->id)->exists();
                if ($is_sobrecerrado) {
                    $isReadOnly = ($is_revisado && $concurso->ronda_actual == 1) || $concurso->ronda_actual > 1;
                    $bloquearInvitacionOferentes = $invitacionesExistentes;
                    
                } elseif ($is_online) {
                    $isReadOnly = ($fechasubasta < $fechaActual); // Asegúrate de usar ">" en lugar de ">="
                } else {
                    $isReadOnly = false; // Asume que los demás casos no tienen restricciones.
                }
            }

            $sheets = [];
            foreach (SheetType::all() as $sheet_type) {
                if ($create) {
                    $sheet = $is_copy ? $concurso->sheets->where('type_id', $sheet_type->id)->first() : null;
                } else {
                    $sheet = $concurso->sheets->where('type_id', $sheet_type->id)->first();
                }
                $sheets[] = [
                    'id' => $sheet ? $sheet->id : null,
                    'filename' => $sheet ? $sheet->filename : null,
                    'type_id' => $sheet_type->id,
                    'type_name' => $sheet_type->description,
                    'action' => null
                ];
            }

            // COMMON
            $list = [
                'Id' => $create ? 0 : $concurso->id,
                'Tipo' => $params['type'],
                'TypeDescription' => Concurso::TYPE_DESCRIPTION[$params['type']],
                'TipoOperaciones' => TipoOperacion::getList(),
                'TipoOperacion' => 2,
                'IsGo' => $is_go,
                'IsSobrecerrado' => $is_sobrecerrado,
                'IsOnline' => $is_online,
                'ReadOnly' => $create
                    ? false 
                    : $isReadOnly,
                'BloquearInvitacionOferentes' => $bloquearInvitacionOferentes,
                'Nombre' => $create && !$is_copy ? '' : $concurso->nombre,
                'AreaUsr' => $create && !$is_copy ? '' : $concurso->area_sol,
                'FechaAlta' => $create ? Carbon::now()->format('Y-m-d H:i:s') : $concurso->fecha_alta->format('Y-m-d H:i:s'),
                'FilePath' => filePath($user->file_path_customer),
                'SolicitudCompra' => $create ? '' : $concurso->solicitud_compra,
                'OrdenCompra' => $create ? '' : $concurso->orden_compra,
                'Resena' => $create && !$is_copy ? '' : $concurso->resena,
                'Descripcion' => $create && !$is_copy ? '' : $concurso->descripcion,
                'DescriptionLimit' => $this::$description_limit,
                'Sheets' => $sheets,
                'Pais' => $create && !$is_copy ? '' : $concurso->pais,
                'Provincia' => $create && !$is_copy ? '' : $concurso->provincia,
                'Localidad' => $create && !$is_copy ? '' : $concurso->localidad,
                'Direccion' => $create && !$is_copy ? '' : $concurso->direccion,
                'Cp' => $create && !$is_copy ? '' : $concurso->cp,
                'Latitud' => $create && !$is_copy ? '' : $concurso->latitud,
                'Longitud' => $create && !$is_copy ? '' : $concurso->longitud,
                'ProductMeasurementList' => Measurement::getList(),
                'Products' => $create && !$is_copy ? [] : $concurso->products_output,
                'TipoConvocatorias' => ConvocatoriaTipo::getList(),
                'TipoConvocatoria' => 1,
                'OferentesAInvitar' =>
                    $create ?
                    [] :
                    $concurso->oferentes->pluck('id_offerer')->toArray(),
                //'FinalizacionConsultas' => $create ? Carbon::now()->addDays(3)->addHour(1)->format('d-m-Y H:i') : $concurso->finalizacion_consultas->format('d-m-Y H:i'),
                'FinalizacionConsultas' => $create
                ? Carbon::now()->addDays(3)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i')
                : $concurso->finalizacion_consultas->format('d-m-Y H:i'),                
                'AceptacionTerminos' => $create && !$is_copy ? 'no' : $concurso->aceptacion_terminos,
                'Aperturasobre' => $create && !$is_copy ? 'no' : $concurso->aperturasobre,
                //'FechaLimite' => $create ? Carbon::now()->addDays(1)->addHour()->format('d-m-Y H:i') : $concurso->fecha_limite->format('d-m-Y H:i'),
                'FechaLimite' => $create 
                ? Carbon::now()->addDays(1)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i')
                : $concurso->fecha_limite->format('d-m-Y H:i'),               
                'SeguroCaucion' => $create && !$is_copy ? 'no' : $concurso->seguro_caucion,
                'DiagramaGant' => $create && !$is_copy ? 'no' : $concurso->diagrama_gant,

                'CertificadoVisitaObra' => $create && !$is_copy ? 'no' : $concurso->cert_visita,
                'ListaProveedores' => $create && !$is_copy ? 'no' : $concurso->lista_prov,

                'UsuariosCalificanReputacion' => $user->getEvaluadoresTecnicaList(),
                'UsuariosSupervisores' => $user->getSupervisoresList(),
                'UsuarioCalificaReputacion' => $create && !$is_copy ? null : $user->getEvaluadoresTecnicalSelected($concurso->usuario_califica_reputacion),
                'UsuarioSupervisor' => $create && !$is_copy ? null : $concurso->usuario_fiscalizador,
                
                'IncluyePrecalifTecnica' => $create && !$is_copy ? 'no' : $concurso->ficha_tecnica_incluye,
                'Monedas' => Moneda::getList(),
                'Moneda' => $create && !$is_copy ? null : $concurso->moneda,
                'HabilitaEnvioInvitaciones' => $habilita_envio_invitaciones,
                'DescripcionTitle' => $create && !$is_copy ? null : $concurso->descriptionTitle,
                'DescripcionDescription' => $create && !$is_copy ? null : $concurso->descriptionDescription,
                'DescripcionURL' => $create && !$is_copy ? null : $concurso->descriptionUrl,
                'DescripcionImagePath' => $create && !$is_copy ? null : $concurso->DescripcionImagePath,
                'BaseCondicionesFirmado' => $create && !$is_copy ? 'no' : $concurso->base_condiciones_firmado,
                'CondicionesGenerales' => $create && !$is_copy ? 'no' : $concurso->condiciones_generales,
                'PliegoTecnico' => $create && !$is_copy ? 'no' : $concurso->pliego_tecnico,
                'AcuerdoConfidencialidad' => $create && !$is_copy ? 'no' : $concurso->acuerdo_confidencialidad,
                'LegajoImpositivo' => $create && !$is_copy ? 'no' : $concurso->legajo_impositivo,
                'AntecedentesReferencias' => $create && !$is_copy ? 'no' : $concurso->antecendentes_referencia,
                'ReporteAccidentes' => $create && !$is_copy ? 'no' : $concurso->reporte_accidentes,
                'EstructuraCostos' => $create && !$is_copy ? 'no' : $concurso->estructura_costos,
                'Apu' => $create && !$is_copy ? 'no' : $concurso->apu,
                'TecnicoOfertas' => $create && !$is_copy ? 'no' : $concurso->tecnico_ofertas,
                'CondicionPago' => $create && !$is_copy ? 'no' : $concurso->condicion_pago,
                'EnvioMuestras' => $create && !$is_copy ? 'no' : $concurso->envio_muestra,
                'nom251' => $create && !$is_copy ? 'no' : $concurso->nom251,
                'distintivo' => $create && !$is_copy ? 'no' : $concurso->distintivo,
                'filtros_sanitarios' => $create && !$is_copy ? 'no' : $concurso->filtros_sanitarios,
                'repse' => $create && !$is_copy ? 'no' : $concurso->repse,
                'poliza' => $create && !$is_copy ? 'no' : $concurso->poliza,
                'primariesgo' => $create && !$is_copy ? 'no' : $concurso->primariesgo,
                'obras_referencias' => $create && !$is_copy ? 'no' : $concurso->obras_referencias,
                'obras_organigrama' => $create && !$is_copy ? 'no' : $concurso->obras_organigrama,
                'obras_equipos' => $create && !$is_copy ? 'no' : $concurso->obras_equipos,
                'obras_cronograma' => $create && !$is_copy ? 'no' : $concurso->obras_cronograma,
                'obras_memoria' => $create && !$is_copy ? 'no' : $concurso->obras_memoria,
                'obras_antecedentes' => $create && !$is_copy ? 'no' : $concurso->obras_antecedentes,
                'tarima_ficha_tecnica' => $create && !$is_copy ? 'no' : $concurso->tarima_ficha_tecnica,
                'tarima_licencia' => $create && !$is_copy ? 'no' : $concurso->tarima_licencia,
                'tarima_nom_144' => $create && !$is_copy ? 'no' : $concurso->tarima_nom_144,
                'tarima_acreditacion' => $create && !$is_copy ? 'no' : $concurso->tarima_acreditacion,
                'concurso_fiscalizado' => $create && !$is_copy ? 'no' : $concurso->concurso_fiscalizado,
                'edificio_balance' => $create && !$is_copy ? 'no' : $concurso->edificio_balance,
                'edificio_iva' => $create && !$is_copy ? 'no' : $concurso->edificio_iva,
                'edificio_cuit' => $create && !$is_copy ? 'no' : $concurso->edificio_cuit,
                'edificio_brochure' => $create && !$is_copy ? 'no' : $concurso->edificio_brochure,
                'edificio_organigrama' => $create && !$is_copy ? 'no' : $concurso->edificio_organigrama,
                'edificio_organigrama_obra' => $create && !$is_copy ? 'no' : $concurso->edificio_organigrama_obra,
                'edificio_subcontratistas' => $create && !$is_copy ? 'no' : $concurso->edificio_subcontratistas,
                'edificio_gestion' => $create && !$is_copy ? 'no' : $concurso->edificio_gestion,
                'edificio_maquinas' => $create && !$is_copy ? 'no' : $concurso->edificio_maquinas,
                
                /// ROUNDS DATE-TIME ///
                'ronda_actual' => $create && !$is_copy ? 'no' : $concurso->ronda_actual,
                
                'segunda_ronda_fecha' => ($create && !$is_copy) //<--- Early return for $create && !$is_copy
                    ? ''
                    : ($concurso->segunda_ronda_fecha_limite //<--- if a date exist in db return it with 'd-m-Y H:i' format, else return empty
                        ? $concurso->segunda_ronda_fecha_limite->format('d-m-Y H:i') 
                        : ''),

                // (Second round is named without _limite because another funtion uses "segunda_ronda_fecha_limite"
                // and i dont want to break shit)

                'tercera_ronda_fecha_limite' => ($create && !$is_copy)
                    ? ''
                    : ($concurso->tercera_ronda_fecha_limite 
                        ? $concurso->tercera_ronda_fecha_limite->format('d-m-Y H:i') 
                        : ''),

                'cuarta_ronda_fecha_limite' => ($create && !$is_copy)
                    ? ''
                    : ($concurso->cuarta_ronda_fecha_limite 
                        ? $concurso->cuarta_ronda_fecha_limite->format('d-m-Y H:i') 
                        : ''),
                'quita_ronda_fecha_limite' => ($create && !$is_copy)
                    ? ''
                    : ($concurso->quita_ronda_fecha_limite 
                        ? $concurso->quita_ronda_fecha_limite->format('d-m-Y H:i') 
                        : ''),
                ///
            
                'Countries' => Pais::getCountries(),

            ];

            // SOBRECERRADO
            if ($is_sobrecerrado) {
                $list = $this->createOrEditBidding($create, $list, $user, $concurso, $is_copy);
            }

            // ONLINE
            if ($is_online) {
                $list = $this->createOrEditAuction($create, $list, $user, $concurso, $is_copy);
            }

            // GO
            if ($is_go) {
                $list = $this->createOrEditGo($create, $list, $user, $concurso, $is_copy);
            }

            // Filters
            $filters = new \StdClass();
            $filters->categorias_con_areas_list = Category::getFromCategoryWithAreasList();
            $filters->areas = [];

            $filters->catalogo_de_materiales_list = Catalogo::getFromCatalogoGroupList($user->customer_company->id);
            $filters->material = null;

            $filters->paises_con_provincias_list = Pais::getCountriesWithProvincesList();
            $filters->provinces = [];

            $filters->cities_list = [];
            $filters->cities = [];

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Concursos', 'url' => null],
                ['description' => Concurso::TYPE_DESCRIPTION[$params['type']], 'url' => '/concursos/cliente/' . $params['type']],
                ['description' => $create ? 'Nuevo' : 'Edición']
            ];

            $success = true;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        $test = Category::getFromCategoryWithAreasList();


        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'filters' => $filters,
                'breadcrumbs' => $breadcrumbs
            ]
        ], $status);
    }

    private function createOrEditBidding($create, $list, $user, $concurso, $is_copy)
    {
        return array_merge($list, [
            'FinalizarSiOferentesCompletaronEconomicas' => $create && !$is_copy ? 'no' : $concurso->finalizar_si_oferentes_completaron_economicas,
            'OfertasParcialesPermitidas' => $create && !$is_copy ? 'no' : $concurso->ofertas_parciales_permitidas,
            'OfertasParcialesCantidadMin' => $create && !$is_copy ? 0 : $concurso->ofertas_parciales_cantidad_min,
            'FechaLimiteEconomicas' =>
                $create
                    ? Carbon::now()->addDays(4)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i')
                    : ($concurso->fecha_limite_economicas
                        ? $concurso->fecha_limite_economicas->minute(0)->second(0)->format('d-m-Y H:i')
                        : null),

            'SegundaRondaHabilita' => $create && !$is_copy ? 'no' : $concurso->segunda_ronda_habilita,
            'SegundaRondaFechaLimite' => $create ? Carbon::now()->addDays(4)->addHours(4)->format('d-m-Y H:i') : (isset($concurso->segunda_ronda_fecha_limite) ? $concurso->segunda_ronda_fecha_limite->format('d-m-Y H:i') : null),
            'ImagePath' => filePath(config('app.images_path')),
            'Portrait' => $create && !$is_copy ? null : $concurso->portrait,
            'FechaLimiteTecnica' => $create
            ? Carbon::now()->addDays(3)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i')
            : ($concurso->ficha_tecnica_fecha_limite
                ? $concurso->ficha_tecnica_fecha_limite->minute(0)->second(0)->format('d-m-Y H:i')
                : null
            ),

            'PlantillasTecnicas' => PlantillaTecnicaTipo::getList(),
            'PlantillaTecnica' => $create && !$is_copy ? null : $concurso->ficha_tecnica_plantilla,
            'PlantillaTecnicaSeleccionada' => $create && !$is_copy ? null : ($concurso->technical_includes ? $concurso->plantilla_tecnica->parsed_items : null),
            'UsuariosEvaluanTecnica' => $user->getEvaluadoresTecnicaList(),
            'UsuarioEvaluaTecnica' => $create && !$is_copy ? null : array_map('intval', explode(',', $concurso->ficha_tecnica_usuario_evalua)),
            'DescripcionImagePath' => filePath(config('app.images_path')),
            'DescripcionPortrait' => $create && !$is_copy ? null : $concurso->portraitDescription,

        ]);
    }

   private function createOrEditAuction($create, $list, $user, $concurso, $is_copy)
    {
        return array_merge($list, [
            'InicioSubasta' => $create ? Carbon::now()->addDays(4)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i') : $concurso->inicio_subasta->format('d-m-Y H:i'),
            'Duracion' => $create && !$is_copy ? null : ($concurso->parsed_duracion[0] . $concurso->parsed_duracion[1]),
            'TiempoAdicional' => $create && !$is_copy ? 0 : $concurso->tiempo_adicional,
            'TiposValoresOfertar' => $this->GetTiposValoresOfertar(),
            'TipoValorOfertar' => $create && !$is_copy ? null : $concurso->tipo_valor_ofertar,
            'Chat' => $create && !$is_copy ? 'no' : ($concurso->chat ? 'si' : 'no'),
            'VerNumOferentesParticipan' => $create && !$is_copy ? 'no' : ($concurso->ver_num_oferentes_participan ? 'si' : 'no'),
            'VerOfertaGanadora' => $create && !$is_copy ? 'no' : ($concurso->ver_oferta_ganadora ? 'si' : 'no'),
            'VerRanking' => $create && !$is_copy ? 'no' : ($concurso->ver_ranking ? 'si' : 'no'),
            'VerTiempoRestante' => $create && !$is_copy ? 'no' : ($concurso->ver_tiempo_restante ? 'si' : 'no'),
            'PermitirAnularOferta' => $create && !$is_copy ? 'no' : ($concurso->permitir_anular_oferta ? 'si' : 'no'),
            'SubastaVistaCiega' => $create && !$is_copy ? 'no' : ($concurso->subastavistaciega ? 'si' : 'no'),
            'PrecioMinimo' => $create && !$is_copy ? null : $concurso->precio_minimo,
            'PrecioMaximo' => $create && !$is_copy ? '' : $concurso->precio_maximo,
            'SoloOfertasMejores' => $create && !$is_copy ? 'no' : ($concurso->solo_ofertas_mejores ? 'si' : 'no'),
            'UnidadMinima' => $create && !$is_copy ? '' : $concurso->unidad_minima,
            'ImagePath' => filePath(config('app.images_path')),
            'Portrait' => $create && !$is_copy ? null : $concurso->portrait,
            'FechaLimiteTecnica' => $create
    ? Carbon::now()->addDays(3)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i')
    : ($concurso->ficha_tecnica_fecha_limite
        ? $concurso->ficha_tecnica_fecha_limite->minute(0)->second(0)->format('d-m-Y H:i')
        : null
    ),

            'PlantillasTecnicas' => PlantillaTecnicaTipo::getList(),
            'PlantillaTecnica' => $create && !$is_copy ? null : $concurso->ficha_tecnica_plantilla,
            'PlantillaTecnicaSeleccionada' => $create && !$is_copy ? null : ($concurso->technical_includes ? $concurso->plantilla_tecnica->parsed_items : null),
            'UsuariosEvaluanTecnica' => $user->getEvaluadoresTecnicaList(),
            'UsuarioEvaluaTecnica' => $create && !$is_copy ? null : array_map('intval', explode(',', $concurso->ficha_tecnica_usuario_evalua)),
        ]);
    }

    private function createOrEditGo($create, $list, $user, $concurso, $is_copy)
    {
        $document_cuota_ap = null;
        $policy_amount_cuota_ap = null;
        $document_no_rep_art = null;
        $document_benef = null;
        $additional_driver_documents = [];
        $additional_vehicle_documents = [];
        if (!$create) {
            $document_cuota_ap = $concurso->go->documents->filter(function ($go_document) {
                return $go_document->document->gcg_code === 'OPTUS_CUOTA AP';
            })->first();

            $document_no_rep_art = $concurso->go->documents->filter(function ($go_document) {
                return $go_document->document->gcg_code === 'NOGCG_NO_REP_ART';
            })->first();

            $document_benef = $concurso->go->documents->filter(function ($go_document) {
                return $go_document->document->gcg_code === 'NOGCG_BENEF';
            })->first();

            $additional_driver_documents = $concurso->go->additional_documents->where('type', GoDocumentAdditional::TYPE_SLUGS['driver']);
            $additional_driver_documents = $additional_driver_documents ? $additional_driver_documents->pluck('name') : [];
            $additional_vehicle_documents = $concurso->go->additional_documents->where('type', GoDocumentAdditional::TYPE_SLUGS['vehicle']);
            $additional_vehicle_documents = $additional_vehicle_documents ? $additional_vehicle_documents->pluck('name') : [];
        }

        return array_merge($list, [
            'PaymentMethods' => GoPaymentMethod::getList(),
            'PaymentMethod' => $create && !$is_copy ? null : $concurso->go->payment_method->id,
            'GoLoadTypes' => GoLoadType::getList(),
            'GoLoadType' => $create && !$is_copy ? 0 : $concurso->go->load_type->id,
            'Peso' => $create && !$is_copy ? null : $concurso->go->peso,
            'Ancho' => $create && !$is_copy ? null : $concurso->go->ancho,
            'Largo' => $create && !$is_copy ? null : $concurso->go->largo,
            'Alto' => $create && !$is_copy ? null : $concurso->go->alto,
            'UnidadesBultos' => $create && !$is_copy ? 0 : $concurso->go->unidades_bultos,
            'PlazoPago' => $create && !$is_copy ? null : $concurso->go->plazo_pago,
            'FechaDesde' => $create ? Carbon::now()->format('d-m-Y H:i') : $concurso->go->fecha_desde->format('d-m-Y H:i'),
            'FechaHasta' => $create ? Carbon::now()->addDays(2)->format('d-m-Y H:i') : $concurso->go->fecha_hasta->format('d-m-Y H:i'),
            'ProvinciasDesde' => Provincia::getList(),
            'ProvinciaDesdeSelect' => $create && !$is_copy ? null : $concurso->go->province_from->id,
            'ProvinciasHasta' => Provincia::getList(),
            'ProvinciaHastaSelect' => $create && !$is_copy ? null : $concurso->go->province_to->id,
             'FechaLimiteEconomicas' =>
            $create
                ? Carbon::now()->addDays(4)->addHour(2)->minute(0)->second(0)->format('d-m-Y H:i')
                : ($concurso->fecha_limite_economicas
                    ? $concurso->fecha_limite_economicas->minute(0)->second(0)->format('d-m-Y H:i')
                    : null),


            'CiudadDesdeSelect' => $create && !$is_copy ? null : $concurso->go->city_from->id,
            'CiudadHastaSelect' => $create && !$is_copy ? null : $concurso->go->city_to->id,
            'CalleDesde' => $create && !$is_copy ? null : $concurso->go->calle_desde,
            'CalleHasta' => $create && !$is_copy ? null : $concurso->go->calle_hasta,
            'NumeracionDesde' => $create && !$is_copy ? null : $concurso->go->numeracion_desde,
            'NumeracionHasta' => $create && !$is_copy ? null : $concurso->go->numeracion_hasta,
            'NombreDesde' => $create && !$is_copy ? null : $concurso->go->nombre_desde,
            'NombreHasta' => $create && !$is_copy ? null : $concurso->go->nombre_hasta,
            'CotizarSeguro' => $create && !$is_copy ? false : $concurso->go->cotizar_seguro,
            'SumaAsegurada' => $create && !$is_copy ? null : $concurso->go->suma_asegurada,
            'CotizarArmada' => $create && !$is_copy ? false : $concurso->go->cotizar_armada,
            'DriverDocuments' => Document::getListGcgDocumentsByType(DocumentType::TYPE_SLUGS['driver']),
            'DriverDocumentsSelected' => $create && !$is_copy ? [] : array_map('strval', $concurso->go->driver_gcg_documents->pluck('id')->toArray()),
            'VehicleDocuments' => Document::getListGcgDocumentsByType(DocumentType::TYPE_SLUGS['vehicle']),
            'VehicleDocumentsSelected' => $create && !$is_copy ? [] : array_map('strval', $concurso->go->vehicle_gcg_documents->pluck('id')->toArray()),
            'Amount' => PolicyAmount::getList(),
            'AmountSelect' => $document_cuota_ap ? $document_cuota_ap->policy_amount->id : null,
            'Ratio' => PolicyAmount::getListRatio(),
            'ClausulaArt' => (bool) $document_no_rep_art,
            'CuitDoc' => $document_no_rep_art ? $document_no_rep_art->cuit : null,
            'RazonSocialDoc' => $document_no_rep_art ? $document_no_rep_art->razon_social : null,
            'ClausulaBeneficiario' => (bool) $document_benef,
            'CuitBeneficiario' => $document_benef ? $document_benef->cuit : null,
            'RazonSocialBeneficiario' => $document_benef ? $document_benef->razon_social : null,
            'AdditionalDriverDocuments' => $additional_driver_documents,
            'AdditionalVehicleDocuments' => $additional_vehicle_documents,
            'FinalizarSiOferentesCompletaronEconomicas' => 'si',
            'FechaLimiteTecnica' => $create ? Carbon::now()->addDays(4)->addHour(2)->format('d-m-Y H:i') : ($concurso->fecha_limite_economicas ?
                $concurso->fecha_limite_economicas->format('d-m-Y H:i') :
                null
            ),
        ]);
    }

    // public function store(Request $request, Response $response, $params)
    // {
    //     $entity = json_decode($request->getParsedBody()['Entity'], false);

    //     $success = false;
    //     $message = null;
    //     $status = 200;
    //     $error = false;

    //     $create = (bool) !(isset($params['id']));

    //     // validamos que se hace, crear o editar
    //     try {
    //         $capsule = dependency('db');
    //         $connection = $capsule->getConnection();
    //         $connection->beginTransaction();

    //         if ($create) {
    //             // Validar si el usuario fiscalizador está habilitado
    //             if ($entity->concurso_fiscalizado == 'no') {
    //                 // Si no se requiere fiscalizador, asegúrate de que no se esté validando
    //                 $entity->UsuarioSupervisor = null; // O manejarlo según tu lógica
    //             } else {
    //                 // Validar si el usuario fiscalizador está habilitado
    //                 if (!$entity->UsuarioSupervisor) {
    //                     return [
    //                         'success' => false,
    //                         'message' => 'Debe seleccionar un fiscalizador si se habilita la opción.',
    //                         'status' => 422
    //                     ];
    //                 }
    //             }

    //             $result = $this->createConcurso($params['type'], $entity);

    //             if ($result['error']) {
    //                 $connection->rollBack();
    //                 $message = $result['message'];
    //                 $status = 422;
    //                 $success = false;
    //             } else {
    //                 $connection->commit();
    //                 $message = 'Concurso guardado con éxito.';
    //                 $success = true;
    //             }
    //         }

    //         if (!$create) {

                
    //             // Validar si el usuario fiscalizador está habilitado
    //             if ($entity->concurso_fiscalizado == 'no') {
    //                 // Si no se requiere fiscalizador, asegúrate de que no se esté validando
    //                 $entity->UsuarioSupervisor = null; // O manejarlo según tu lógica
    //             } else {
    //                 // Validar si el usuario fiscalizador está habilitado
    //                 if (!$entity->UsuarioSupervisor) {
    //                     return [
    //                         'success' => false,
    //                         'message' => 'Debe seleccionar un fiscalizador si se habilita la opción.',
    //                         'status' => 422
    //                     ];
    //                 }
    //             }
                
    //             $result = $this->editConcurso($params['type'], $params['id'], $entity);

    //             if ($result['error']) {
    //                 $connection->rollBack();
    //                 $status = 422;
    //                 $success = false;
    //                 $message = $result['message'];
    //             } else {
    //                 $concurso = $result['data']['concurso'];
    //                 $oferentes = [];
    //                 foreach ($concurso->oferentes as $oferente) {
    //                     if (!$oferente->is_concurso_rechazado && !$oferente->is_seleccionado) {
    //                         array_push($oferentes, $oferente);
    //                     }
    //                 }

    //                 if (count($oferentes) === 0) {
    //                     $result = [
    //                         'success' => true
    //                     ];
    //                 }

    //                 if (count($oferentes) > 0) {

    //                     $ajustdate = $result['data']['ajustdate'];
    //                     $documentChange = $result['data']['documentsChanged']['documentChange'];
    //                     $documentDeleted = $result['data']['documentsChanged']['documentDeleted'];
    //                     $productsDeleted = $result['data']['products_results']['productsDeleted'];
    //                     $productsUpdated = $result['data']['products_results']['productsUpdated'];
    //                     $productsNew = $result['data']['products_results']['productsNew'];

    //                     if (isset($result['data']['payroll_results'])) {
    //                         $technicalAdded = $result['data']['payroll_results']['technicalAdded'];
    //                         $technicalDeleted = $result['data']['payroll_results']['technicalDeleted'];
    //                         $technicalChanged = $result['data']['payroll_results']['technicalChanged'];
    //                     } else {
    //                         $technicalAdded = false;
    //                         $technicalDeleted = false;
    //                         $technicalChanged = false;
    //                     }

    //                     $tecnicalDocuments = $result['data']['tecnicalDocuments'];
    //                     $ajustDocumentsEconomica = $result['data']['ajustDocumentsEconomica'];

    //                     if (
    //                         !$ajustdate && !$documentChange && !$documentDeleted && !$technicalAdded && !$technicalDeleted && !$technicalChanged && count($productsDeleted) == 0 &&
    //                         count($productsUpdated) == 0 && count($productsNew) == 0 && count($tecnicalDocuments) == 0 && count($ajustDocumentsEconomica) == 0
    //                     ) {
    //                         $result = [
    //                             'success' => true
    //                         ];
    //                     } else {
    //                         $tipoConcurso = null;
    //                         if ($concurso->is_sobrecerrado)
    //                             $tipoConcurso = 'Licitación';
    //                         if ($concurso->is_online)
    //                             $tipoConcurso = 'Subasta';
    //                         if ($concurso->is_go)
    //                             $tipoConcurso = 'Go';
    //                         $title = 'Ajustes en ' . $tipoConcurso;
    //                         $subject = $concurso->nombre . ' - ' . $title;
    //                         $htmlBody = [
    //                             'title' => $title,
    //                             'ano' => Carbon::now()->format('Y'),
    //                             'concurso' => $concurso,
    //                             'tipoConcurso' => $tipoConcurso,
    //                             'ajustdates' => false,
    //                             'documentChange' => false,
    //                             'documentDeleted' => false,
    //                             'technicalAdded' => false,
    //                             'technicalDeleted' => false,
    //                             'technicalChanged' => false,
    //                             'productsDeleted' => false,
    //                             'productsUpdated' => false,
    //                             'productsNew' => false,
    //                             'tecnicalDocuments' => false,
    //                             'ajustDocumentsEconomica' => false,
    //                             'listProductsDeleted' => [],
    //                             'listProductsUpdated' => [],
    //                             'listProductsNew' => [],
    //                             'listTecnicalDocuments' => [],
    //                             'listDocumentsEconomica' => [],
    //                             'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('d-m-Y H:i') : 'No aplica',
    //                             'company_name' => null

    //                         ];


    //                         $template = rootPath(config('app.templates_path')) . '/email/date-change.tpl';

    //                         if ($ajustdate) {
    //                             $htmlBody['ajustdates'] = true;
    //                         }

    //                         if ($documentChange) {
    //                             $htmlBody['documentChange'] = true;
    //                         }

    //                         if ($documentDeleted) {
    //                             $htmlBody['documentDeleted'] = true;
    //                         }

    //                         if ($technicalAdded) {
    //                             $htmlBody['technicalAdded'] = true;
    //                         }

    //                         if ($technicalDeleted) {
    //                             $htmlBody['technicalDeleted'] = true;
    //                         }

    //                         if ($technicalChanged) {
    //                             $htmlBody['technicalChanged'] = true;
    //                         }

    //                         if (count($productsDeleted) > 0) {
    //                             $listProductsDeleted = [];
    //                             foreach ($productsDeleted as $value) {
    //                                 $listProductsDeleted[] = $value->toArray();
    //                             }
    //                             $htmlBody['productsDeleted'] = true;
    //                             $htmlBody['listProductsDeleted'] = $listProductsDeleted;
    //                         }

    //                         if (count($productsUpdated) > 0) {
    //                             $listProductsUpdated = [];
    //                             foreach ($productsUpdated as $value) {
    //                                 $listProductsUpdated[] = $value;
    //                             }
    //                             $htmlBody['productsUpdated'] = true;
    //                             $htmlBody['listProductsUpdated'] = $listProductsUpdated;
    //                         }

    //                         if (count($productsNew) > 0) {
    //                             $listProductsNew = [];
    //                             foreach ($productsNew as $value) {
    //                                 $listProductsNew[] = $value;
    //                             }
    //                             $htmlBody['productsNew'] = true;
    //                             $htmlBody['listProductsNew'] = $listProductsNew;
    //                         }

    //                         if (count($tecnicalDocuments) > 0) {
    //                             $htmlBody['tecnicalDocuments'] = true;
    //                             $htmlBody['listTecnicalDocuments'] = $tecnicalDocuments;
    //                         }

    //                         if (count($ajustDocumentsEconomica) > 0) {
    //                             $htmlBody['ajustDocumentsEconomica'] = true;
    //                             $htmlBody['listDocumentsEconomica'] = $ajustDocumentsEconomica;
    //                         }



    //                         $emailService = new EmailService();


    //                         //Enviamos el mail a los oferentes que se envio Invitación
    //                         foreach ($oferentes as $oferente) {
    //                             $users = $oferente->company->users->pluck('email');
    //                             $htmlBody['company_name'] = $oferente->company->business_name;
    //                             if ($ajustdate) {
    //                                 if ($oferente->has_invitacion_vencida || $oferente->is_invitacion_pendiente) {
    //                                     $invitation = $oferente->invitation;
    //                                     $invitation_status = InvitationStatus::where('code', InvitationStatus::CODES['pending'])->first();
    //                                     $invitation->update([
    //                                         'status_id' => $invitation_status->id
    //                                     ]);
    //                                 }
    //                             }

    //                             // si se elimina la etapa tecnica los proveedores pasan a etapa economica
    //                             if ($technicalDeleted) {
    //                                 if ($oferente->has_invitacion_aceptada && !$oferente->has_tecnica_rechazada) {
    //                                     $oferente->update([
    //                                         'etapa_actual' => Participante::ETAPAS['economica-pendiente']
    //                                     ]);
    //                                     $technical_proposal = $oferente->technical_proposal;
    //                                     if ($technical_proposal) {
    //                                         $technical_proposal->delete();
    //                                         $technical_proposal->refresh();
    //                                     }
    //                                     $oferente->refresh();
    //                                 }
    //                             }

    //                             // si se edita y los proveedores estan en etapa economica se pasan a tecnica
    //                             if ($technicalAdded || $technicalChanged) {
    //                                 if ($oferente->has_invitacion_aceptada && !$oferente->has_tecnica_rechazada) {
    //                                     $oferente->update([
    //                                         'etapa_actual' => Participante::ETAPAS['tecnica-pendiente']
    //                                     ]);
    //                                     $technical_proposal = $oferente->technical_proposal;
    //                                     if ($technical_proposal) {
    //                                         $technical_proposal->delete();
    //                                         $technical_proposal->refresh();
    //                                     }
    //                                     $oferente->refresh();
    //                                 }
    //                             }

    //                             // si se edita los documentos de la tecnica y los proveedores estan en etapa economica se pasan a tecnica
    //                             if (count($tecnicalDocuments) > 0) {
    //                                 if ($oferente->has_invitacion_aceptada && !$oferente->has_tecnica_rechazada) {
    //                                     $oferente->update([
    //                                         'etapa_actual' => Participante::ETAPAS['tecnica-pendiente']
    //                                     ]);
    //                                     $technical_proposal = $oferente->technical_proposal;
    //                                     if ($technical_proposal) {
    //                                         $technical_proposal->delete();
    //                                         $technical_proposal->refresh();
    //                                     }
    //                                     $oferente->refresh();
    //                                 }
    //                             }

    //                             if (count($productsDeleted) > 0 || count($productsUpdated) > 0 || count($productsNew) > 0) {
    //                                 if (
    //                                     $oferente->has_invitacion_aceptada &&
    //                                     (
    //                                         $concurso->technical_includes && $oferente->has_tecnica_aprobada
    //                                     ) ||
    //                                     (
    //                                         !$concurso->technical_includes &&
    //                                         (
    //                                             $oferente->has_economica_presentada || $oferente->has_economica_revisada
    //                                         )
    //                                     )
    //                                 ) {
    //                                     $oferente->update([
    //                                         'etapa_actual' => Participante::ETAPAS['economica-pendiente']
    //                                     ]);
    //                                     $economic_proposal = $oferente->economic_proposal;
    //                                     if ($economic_proposal) {
    //                                         $economic_proposal->delete();
    //                                         $economic_proposal->refresh();
    //                                     }
    //                                     $oferente->refresh();
    //                                 }
    //                             }
    //                             $html = $this->fetch($template, $htmlBody);
    //                             $result = $emailService->send(
    //                                 $html,
    //                                 $subject,
    //                                 $users,
    //                                 ""
    //                             );
    //                         }
    //                     }
    //                 }

    //                 if ($result['success']) {
    //                     $connection->commit();
    //                     $message = 'Concurso guardado con éxito.';
    //                     $success = true;
    //                 } else {
    //                     $connection->rollBack();
    //                     $message = 'Ha ocurrido un error al enviar las notificaciones';
    //                     $success = false;
    //                 }
    //             }
    //         }
    //     } catch (Exception $e) {
    //         $connection->rollBack();
    //         $success = false;
    //         $message = $e->getMessage();
    //         $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
    //         //$status = 500;
    //     }
    //     $result = [
    //         'success' => $success,
    //         'message' => $message,
    //         'data' => [
    //             'redirect' => ''
    //         ]
    //     ];

    //     return $this->json($response, $result, $status);
    // }
    public function store(Request $request, Response $response, $params)
    {
        $entity = json_decode($request->getParsedBody()['Entity'], false);

        $success = false;
        $message = null;
        $status = 200;
        $error = false;

        $create = (bool) !(isset($params['id']));

        // validamos que se hace, crear o editar
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            if ($create) {
                // Validar si el usuario fiscalizador está habilitado
                if ($entity->concurso_fiscalizado == 'no') {
                    // Si no se requiere fiscalizador, asegúrate de que no se esté validando
                    $entity->UsuarioSupervisor = null; // O manejarlo según tu lógica
                } else {
                    // Validar si el usuario fiscalizador está habilitado
                    if (!$entity->UsuarioSupervisor) {
                        return [
                            'success' => false,
                            'message' => 'Debe seleccionar un fiscalizador si se habilita la opción.',
                            'status' => 422
                        ];
                    }
                }

$result = $this->createConcurso($params['type'], $entity);

if ($result['error']) {
    $connection->rollBack();
    $message = $result['message'];
    $status = 422;
    $success = false;
} else {
    $connection->commit();
    $message = 'Concurso guardado con éxito.';
    $success = true;

    // Enviar email de confirmación al usuario que creó el concurso
    $user = user();
    $concurso = $result['data']['concurso'] ?? null;

    // ✅ IMPORTANTE: asegurar que el objeto tenga los oferentes cargados
    if ($concurso && !$concurso->relationLoaded('oferentes')) {
        $concurso->load('oferentes.company');
    }

    $oferentesNombres = [];
    if ($concurso && $concurso->oferentes && $concurso->oferentes->count() > 0) {
        foreach ($concurso->oferentes as $oferente) {
            $oferentesNombres[] = $oferente->company->business_name ?? 'Sin nombre';
        }
    }

    $templateUsuario = rootPath(config('app.templates_path')) . '/email/confirmation-creation-client.tpl';

    $htmlUser = $this->fetch($templateUsuario, [
        'user' => $user,
        'ano' => Carbon::now()->format('Y'),
        'concurso' => $concurso,
        'title' => 'Confirmación de Creación de Concurso',
        'oferentes' => $oferentesNombres, // 👈 ahora sí, se pasa al email
    ]);

    $emailService = new EmailService();
    $emailService->send($htmlUser, 'Confirmación de Creación de Concurso', [$user->email], "");
}

            }

            if (!$create) {

                
                // Validar si el usuario fiscalizador está habilitado
                if ($entity->concurso_fiscalizado == 'no') {
                    // Si no se requiere fiscalizador, asegúrate de que no se esté validando
                    $entity->UsuarioSupervisor = null; // O manejarlo según tu lógica
                } else {
                    // Validar si el usuario fiscalizador está habilitado
                    if (!$entity->UsuarioSupervisor) {
                        return [
                            'success' => false,
                            'message' => 'Debe seleccionar un fiscalizador si se habilita la opción.',
                            'status' => 422
                        ];
                    }
                }
                
                $result = $this->editConcurso($params['type'], $params['id'], $entity);

                if ($result['error']) {
                    $connection->rollBack();
                    $status = 422;
                    $success = false;
                    $message = $result['message'];
                } else {
                    $concurso = $result['data']['concurso'];
                    $oferentes = [];
                    foreach ($concurso->oferentes as $oferente) {
                        if (!$oferente->is_concurso_rechazado && !$oferente->is_seleccionado) {
                            array_push($oferentes, $oferente);
                        }
                    }

                    if (count($oferentes) === 0) {
                        $result = [
                            'success' => true
                        ];
                    }

                    if (count($oferentes) > 0) {

                        $ajustdate = $result['data']['ajustdate'];
                        $documentChange = $result['data']['documentsChanged']['documentChange'];
                        $documentDeleted = $result['data']['documentsChanged']['documentDeleted'];
                        $productsDeleted = $result['data']['products_results']['productsDeleted'];
                        $productsUpdated = $result['data']['products_results']['productsUpdated'];
                        $productsNew = $result['data']['products_results']['productsNew'];

                        if (isset($result['data']['payroll_results'])) {
                            $technicalAdded = $result['data']['payroll_results']['technicalAdded'];
                            $technicalDeleted = $result['data']['payroll_results']['technicalDeleted'];
                            $technicalChanged = $result['data']['payroll_results']['technicalChanged'];
                        } else {
                            $technicalAdded = false;
                            $technicalDeleted = false;
                            $technicalChanged = false;
                        }

                        $tecnicalDocuments = $result['data']['tecnicalDocuments'];
                        $ajustDocumentsEconomica = $result['data']['ajustDocumentsEconomica'];

                        if (
                            !$ajustdate && !$documentChange && !$documentDeleted && !$technicalAdded && !$technicalDeleted && !$technicalChanged && count($productsDeleted) == 0 &&
                            count($productsUpdated) == 0 && count($productsNew) == 0 && count($tecnicalDocuments) == 0 && count($ajustDocumentsEconomica) == 0
                        ) {
                            $result = [
                                'success' => true
                            ];
                        } else {
                            $tipoConcurso = null;
                            if ($concurso->is_sobrecerrado)
                                $tipoConcurso = 'Licitación';
                            if ($concurso->is_online)
                                $tipoConcurso = 'Subasta';
                            if ($concurso->is_go)
                                $tipoConcurso = 'Go';
                            $title = 'Ajustes en ' . $tipoConcurso;
                            $subject = $concurso->nombre . ' - ' . $title;
                            $htmlBody = [
                                'title' => $title,
                                'ano' => Carbon::now()->format('Y'),
                                'concurso' => $concurso,
                                'tipoConcurso' => $tipoConcurso,
                                'ajustdates' => false,
                                'documentChange' => false,
                                'documentDeleted' => false,
                                'technicalAdded' => false,
                                'technicalDeleted' => false,
                                'technicalChanged' => false,
                                'productsDeleted' => false,
                                'productsUpdated' => false,
                                'productsNew' => false,
                                'tecnicalDocuments' => false,
                                'ajustDocumentsEconomica' => false,
                                'listProductsDeleted' => [],
                                'listProductsUpdated' => [],
                                'listProductsNew' => [],
                                'listTecnicalDocuments' => [],
                                'listDocumentsEconomica' => [],
                                'fecha_tecnica' => $concurso->technical_includes ? $concurso->ficha_tecnica_fecha_limite->format('d-m-Y H:i') : 'No aplica',
                                'company_name' => null

                            ];


                            $template = rootPath(config('app.templates_path')) . '/email/date-change.tpl';

                            if ($ajustdate) {
                                $htmlBody['ajustdates'] = true;
                            }

                            if ($documentChange) {
                                $htmlBody['documentChange'] = true;
                            }

                            if ($documentDeleted) {
                                $htmlBody['documentDeleted'] = true;
                            }

                            if ($technicalAdded) {
                                $htmlBody['technicalAdded'] = true;
                            }

                            if ($technicalDeleted) {
                                $htmlBody['technicalDeleted'] = true;
                            }

                            if ($technicalChanged) {
                                $htmlBody['technicalChanged'] = true;
                            }

                            if (count($productsDeleted) > 0) {
                                $listProductsDeleted = [];
                                foreach ($productsDeleted as $value) {
                                    $listProductsDeleted[] = $value->toArray();
                                }
                                $htmlBody['productsDeleted'] = true;
                                $htmlBody['listProductsDeleted'] = $listProductsDeleted;
                            }

                            if (count($productsUpdated) > 0) {
                                $listProductsUpdated = [];
                                foreach ($productsUpdated as $value) {
                                    $listProductsUpdated[] = $value;
                                }
                                $htmlBody['productsUpdated'] = true;
                                $htmlBody['listProductsUpdated'] = $listProductsUpdated;
                            }

                            if (count($productsNew) > 0) {
                                $listProductsNew = [];
                                foreach ($productsNew as $value) {
                                    $listProductsNew[] = $value;
                                }
                                $htmlBody['productsNew'] = true;
                                $htmlBody['listProductsNew'] = $listProductsNew;
                            }

                            if (count($tecnicalDocuments) > 0) {
                                $htmlBody['tecnicalDocuments'] = true;
                                $htmlBody['listTecnicalDocuments'] = $tecnicalDocuments;
                            }

                            if (count($ajustDocumentsEconomica) > 0) {
                                $htmlBody['ajustDocumentsEconomica'] = true;
                                $htmlBody['listDocumentsEconomica'] = $ajustDocumentsEconomica;
                            }



                            $emailService = new EmailService();


                            //Enviamos el mail a los oferentes que se envio Invitación
                            foreach ($oferentes as $oferente) {
                                $users = $oferente->company->users->pluck('email');
                                $htmlBody['company_name'] = $oferente->company->business_name;
                                if ($ajustdate) {
                                    if ($oferente->has_invitacion_vencida || $oferente->is_invitacion_pendiente) {
                                        $invitation = $oferente->invitation;
                                        $invitation_status = InvitationStatus::where('code', InvitationStatus::CODES['pending'])->first();
                                        $invitation->update([
                                            'status_id' => $invitation_status->id
                                        ]);
                                    }
                                }

                                // si se elimina la etapa tecnica los proveedores pasan a etapa economica
                                if ($technicalDeleted) {
                                    if ($oferente->has_invitacion_aceptada && !$oferente->has_tecnica_rechazada) {
                                        $oferente->update([
                                            'etapa_actual' => Participante::ETAPAS['economica-pendiente']
                                        ]);
                                        $technical_proposal = $oferente->technical_proposal;
                                        if ($technical_proposal) {
                                            $technical_proposal->delete();
                                            $technical_proposal->refresh();
                                        }
                                        $oferente->refresh();
                                    }
                                }

                                // si se edita y los proveedores estan en etapa economica se pasan a tecnica
                                if ($technicalAdded || $technicalChanged) {
                                    if ($oferente->has_invitacion_aceptada && !$oferente->has_tecnica_rechazada) {
                                        $oferente->update([
                                            'etapa_actual' => Participante::ETAPAS['tecnica-pendiente']
                                        ]);
                                        $technical_proposal = $oferente->technical_proposal;
                                        if ($technical_proposal) {
                                            $technical_proposal->delete();
                                            $technical_proposal->refresh();
                                        }
                                        $oferente->refresh();
                                    }
                                }

                                // si se edita los documentos de la tecnica y los proveedores estan en etapa economica se pasan a tecnica
                                if (count($tecnicalDocuments) > 0) {
                                    if ($oferente->has_invitacion_aceptada && !$oferente->has_tecnica_rechazada) {
                                        $oferente->update([
                                            'etapa_actual' => Participante::ETAPAS['tecnica-pendiente']
                                        ]);
                                        $technical_proposal = $oferente->technical_proposal;
                                        if ($technical_proposal) {
                                            $technical_proposal->delete();
                                            $technical_proposal->refresh();
                                        }
                                        $oferente->refresh();
                                    }
                                }

                                if (count($productsDeleted) > 0 || count($productsUpdated) > 0 || count($productsNew) > 0) {
                                    if (
                                        $oferente->has_invitacion_aceptada &&
                                        (
                                            $concurso->technical_includes && $oferente->has_tecnica_aprobada
                                        ) ||
                                        (
                                            !$concurso->technical_includes &&
                                            (
                                                $oferente->has_economica_presentada || $oferente->has_economica_revisada
                                            )
                                        )
                                    ) {
                                        $oferente->update([
                                            'etapa_actual' => Participante::ETAPAS['economica-pendiente']
                                        ]);
                                        $economic_proposal = $oferente->economic_proposal;
                                        if ($economic_proposal) {
                                            $economic_proposal->delete();
                                            $economic_proposal->refresh();
                                        }
                                        $oferente->refresh();
                                    }
                                }
                                $html = $this->fetch($template, $htmlBody);
                                $result = $emailService->send(
                                    $html,
                                    $subject,
                                    $users,
                                    ""
                                );
                            }
                        }
                    }

                    if ($result['success']) {
                        $connection->commit();
                        $message = 'Concurso guardado con éxito.';
                        $success = true;
                        // Enviar email de confirmación al usuario que editó
                        $user = user();
                        $templateUsuario = rootPath(config('app.templates_path')) . '/email/edition-confirmation-client.tpl';
                        $htmlUser = $this->fetch($templateUsuario, [
                            'user' => $user,
                            'concurso' => $concurso,
                            'title' => 'Confirmación de Edición de Concurso',
                            'ano' => Carbon::now()->format('Y')
                        ]);
                        $emailService = new EmailService();
                        $emailService->send($htmlUser, 'Confirmación de Edición de Concurso', [$user->email], "");

                    } else {
                        $connection->rollBack();
                        $message = 'Ha ocurrido un error al enviar las notificaciones';
                        $success = false;
                    }
                }
            }
        } catch (Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
            //$status = 500;
        }
        $result = [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => ''
            ]
        ];

        return $this->json($response, $result, $status);
    }

    private function storeBidding($entity, $extra_fields)
    {
        $result = [];
    
        try {
            //Store round dates in array
            $fechaLimiteEconomicas = null;
            $roundDates = [
                $entity->quita_ronda_fecha_limite ?? null,
                $entity->cuarta_ronda_fecha_limite ?? null,
                $entity->tercera_ronda_fecha_limite ?? null,
                $entity->segunda_ronda_fecha ?? null,
            ];
    
            //Loop the array, if one date is found then we are on a new round, so we store the first date that is present, then break.
            //If for some reason another round is added in the future, add it first in the array :)
            foreach ($roundDates as $date) {
                if (!empty($date)) {
                    $fechaLimiteEconomicas = Carbon::createFromFormat('d-m-Y H:i', $date)->format('Y-m-d H:i:s');
                    break;
                }
            }
    
            // If no round dates has been passed, we asume we are either in createConcurso() 
            // or the current round is = 1, which means this is an edit. In both cases we use the knockout data from the viewmodel
            if (empty($fechaLimiteEconomicas) && !empty($entity->FechaLimiteEconomicas)) {
                $fechaLimiteEconomicas = Carbon::createFromFormat('d-m-Y H:i', $entity->FechaLimiteEconomicas)->format('Y-m-d H:i:s');
            }
    
            //Extra_fields for the 'sobrecerrado'
            $result = array_merge(is_array($extra_fields) ? $extra_fields : [], [
                'finalizar_si_oferentes_completaron_economicas' => $entity->FinalizarSiOferentesCompletaronEconomicas ?? null,
                'ofertas_parciales_permitidas' => $entity->OfertasParcialesPermitidas ?? null,
                'ofertas_parciales_cantidad_min' => $entity->OfertasParcialesCantidadMin ?? null,
    
                'fecha_limite_economicas' => $fechaLimiteEconomicas,
    
                'segunda_ronda_fecha_limite' => !empty($entity->segunda_ronda_fecha)
                    ? Carbon::createFromFormat('d-m-Y H:i', $entity->segunda_ronda_fecha)->format('Y-m-d H:i:s')
                    : null,
    
                'tercera_ronda_fecha_limite' => !empty($entity->tercera_ronda_fecha_limite)
                    ? Carbon::createFromFormat('d-m-Y H:i', $entity->tercera_ronda_fecha_limite)->format('Y-m-d H:i:s')
                    : null,
    
                'cuarta_ronda_fecha_limite' => !empty($entity->cuarta_ronda_fecha_limite)
                    ? Carbon::createFromFormat('d-m-Y H:i', $entity->cuarta_ronda_fecha_limite)->format('Y-m-d H:i:s')
                    : null,
    
                'quita_ronda_fecha_limite' => !empty($entity->quita_ronda_fecha_limite)
                    ? Carbon::createFromFormat('d-m-Y H:i', $entity->quita_ronda_fecha_limite)->format('Y-m-d H:i:s')
                    : null,
    
                'ficha_tecnica_fecha_limite' => ($entity->IncluyePrecalifTecnica === 'si' && !empty($entity->FechaLimiteTecnica))
                    ? Carbon::createFromFormat('d-m-Y H:i', $entity->FechaLimiteTecnica)->format('Y-m-d H:i:s')
                    : null,
    
                'segunda_ronda_habilita' => $entity->SegundaRondaHabilita ?? 'no',
                'imagen' => isset($entity->Portrait) ? ($entity->Portrait->filename ?? null) : null,
                'solicitud_compra' => $entity->SolicitudCompra ?? null,
                'orden_compra' => $entity->OrdenCompra ?? null,
                'ficha_tecnica_usuario_evalua' => isset($entity->UsuarioEvaluaTecnica) ? implode(',', $entity->UsuarioEvaluaTecnica) : null,
                'ficha_tecnica_plantilla' => $entity->PlantillaTecnica ?? null,
                'descriptionImagen' => isset($entity->DescripcionPortrait) ? ($entity->DescripcionPortrait->filename ?? null) : null,
            ]);
    
        } catch (\Throwable $e) {
            //This store funtion is the most posible cause of error when saving the data, so i left a .txt
            $txt = fopen('error-storeBidding().txt', 'w');
            fwrite($txt, json_encode([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            fclose($txt);
        }
    
        //Return the data to createConcurso() / editConcurso()
        return array_filter($result, function ($value) {
            return !is_null($value);
        });
    }
    

    private function storeAuction($entity, $extra_fields)
    {


        $duracion_split = $entity->Duracion ? str_split($entity->Duracion, 3) : [];
        $duracion = count($duracion_split) > 0 ? (int) ($duracion_split[0] * 60) + (int) $duracion_split[1] : 0;
        return array_merge($extra_fields, [
            'inicio_subasta' =>
                $entity->InicioSubasta ?
                Carbon::createFromFormat('d-m-Y H:i', $entity->InicioSubasta)->format('Y-m-d H:i:s') :
                null,
            'duracion' => $duracion,
            'tiempo_adicional' => $entity->TiempoAdicional,
            'plantilla_economicas' => isset($entity->PlantillasEconomica) ? $entity->PlantillasEconomica : null,
            'tipo_valor_ofertar' => isset($entity->TipoValorOfertar) ? $entity->TipoValorOfertar : null,
            'chat' => $entity->Chat,
            'subastavistaciega' => isset($entity->SubastaVistaCiega) ? $entity->SubastaVistaCiega : 'no',
            'ver_num_oferentes_participan' => $entity->VerNumOferentesParticipan,
            'ver_oferta_ganadora' => $entity->VerOfertaGanadora,
            'ver_ranking' => $entity->VerRanking,
            'ver_tiempo_restante' => $entity->VerTiempoRestante,
            'permitir_anular_oferta' => $entity->PermitirAnularOferta,
            'precio_maximo' => $entity->PrecioMaximo,
            'precio_minimo' => $entity->PrecioMinimo,
            'solo_ofertas_mejores' => $entity->SoloOfertasMejores,
            'unidad_minima' => $entity->UnidadMinima,
            'imagen' => $entity->Portrait->filename,
            'solicitud_compra' => $entity->SolicitudCompra,
            'orden_compra' => $entity->OrdenCompra,
            'ficha_tecnica_fecha_limite' =>
                $entity->IncluyePrecalifTecnica === 'si' ?
                ($entity->FechaLimiteTecnica ?
                    Carbon::createFromFormat('d-m-Y H:i', $entity->FechaLimiteTecnica)->format('Y-m-d H:i:s') :
                    null
                ) :
                null,
            'ficha_tecnica_usuario_evalua' => isset($entity->UsuarioEvaluaTecnica) ? implode(',', $entity->UsuarioEvaluaTecnica) : null,
            'ficha_tecnica_plantilla' => isset($entity->PlantillaTecnica) ? $entity->PlantillaTecnica : null,
        ]);

    }

    private function storeGO($entity, $extra_fields)
    {
        return array_merge($extra_fields, [
            'finalizar_si_oferentes_completaron_economicas' => 'si',
            'ofertas_parciales_permitidas' => "no",
            'ofertas_parciales_cantidad_min' => 0,
            'fecha_limite_economicas' =>
                $entity->FechaLimiteEconomicas ?
                Carbon::createFromFormat('d-m-Y H:i', $entity->FechaLimiteEconomicas)->format('Y-m-d H:i:s') :
                null,
            'segunda_ronda_habilita' => 'no',
            'segunda_ronda_fecha_limite' => null,
            'ficha_tecnica_fecha_limite' => $entity->FechaLimiteEconomicas ? Carbon::createFromFormat('d-m-Y H:i', $entity->FechaLimiteEconomicas)->format('Y-m-d H:i:s') : null,
        ]);
    }

    //This comment is there so you can find the funtion for new rounds!
    //I know, this should be sendNewRound(), but we didnt have time to fix the routes.
    public function sendSecondRound(Request $request, Response $response)
    {
        $success = false;
        $message = '';
        $status = 200;
        $redirect_url = null;
        $result = [];
    
        //The data comes from templates/concurso/detail/customer/detail.tpl => "SendNewRound()"
        try {
            $body = json_decode($request->getParsedBody()['Data']);
            $concurso = Concurso::find((int) $body->ConcursoId);

            if (!$concurso->is_sobrecerrado) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Solo aplica para sobres cerrados.',
                ], 422);
            }
    
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
    
            $emailService = new EmailService();
            $rondaActual = $concurso->ronda_actual;
            $nuevaRonda = $rondaActual + 1;
    
            //Dynamic Validation
            $fechaNuevaCampo = Concurso::CAMPOS_FECHA_NUEVA_RONDA[$nuevaRonda] ?? null;
            $comentarioCampo = Concurso::CAMPOS_COMENTARIOS_NUEVA_RONDA[$nuevaRonda] ?? null;
    
            if (!$fechaNuevaCampo || !$comentarioCampo) {
                throw new Exception("Configuración inválida para la ronda $nuevaRonda.");
            }
    
            //New dates for the round
            $dateNewRound = $body->NewRoundDate
                ? Carbon::createFromFormat('d-m-Y H:i', $body->NewRoundDate)->format('Y-m-d H:i:s')
                : null;
    
            $currentFechaCierreMuroConsulta = $concurso->finalizacion_consultas;
            $dateNewMuroConsulta = $body->NuevaFechaCierreMuroConsulta
                ? Carbon::createFromFormat('d-m-Y H:i', $body->NuevaFechaCierreMuroConsulta)->format('Y-m-d H:i:s')
                : $currentFechaCierreMuroConsulta;
    
            /*
            //Deprecated, now dateNewEconomicProposal = dateNewRound
            $dateNewEconomicProposal = $body->NuevaFechaLimitePropuestasEconomicas
                ? Carbon::createFromFormat('d-m-Y H:i', $body->NuevaFechaLimitePropuestasEconomicas)->format('Y-m-d H:i:s')
                : null;
    
            $isoDate = $body->NewRoundMinimunDate; // e.g. "2025-04-19T17:29:09.527Z"
            $formattedDate = Carbon::parse($isoDate)->setTimezone('Europe/Paris');
            $dateLimit = $formattedDate->format('d-m-Y H:i');
            */
    
            $validator = validator(
                $data = [
                    $fechaNuevaCampo => $dateNewRound,
                    $comentarioCampo => $body->CommentNewRound
                ],
                $rules = [
                    $fechaNuevaCampo => 'required|date_format:Y-m-d H:i:s',
                    $comentarioCampo => 'required'
                ],
                $messages = [
                    "$fechaNuevaCampo.required" => "Debe ingresar una fecha valida para la nueva ronda",
                    "$comentarioCampo.required" => "Debe ingresar un comentario para la nueva ronda de oferta"
                ]
            );

    
            //Error Management
            if ($validator->fails()) {
                $success = false;
                $status = 422;
                $message = $validator->errors()->first();
                $connection->rollBack();
            } else {
                //Update Contest
                $updateConcurso = [
                    'ronda_actual' => $nuevaRonda,
                    'segunda_ronda_habilita' => 'si',
                    'finalizacion_consultas' => $dateNewMuroConsulta,
                    'fecha_limite_economicas' => $dateNewRound,
                    $fechaNuevaCampo => $dateNewRound,
                    $comentarioCampo => $body->CommentNewRound
                ];
                
                $concurso->update($updateConcurso);
                
                // 2) Para la 2ª ronda, comprobamos directamente en BD si quedan oferentes en 'economica-revisada'
                if ($nuevaRonda === 2) {
                    $pendientesCount = $concurso->oferentes()
                        ->where('etapa_actual', Participante::ETAPAS['economica-revisada'])
                        ->count();

                    if ($pendientesCount === 0) {
                        $connection->commit();
                        return $this->json($response, [
                            'success' => true,
                            'message' => 'No hay oferentes pendientes para la segunda ronda.',
                        ], 200);
                    }
                }
                
                //Mailer to offer
                $rondaTitle = Participante::RONDAS[$nuevaRonda];
                $title = "$rondaTitle oferta económicas habilitada";
                $subject = $concurso->nombre . ' - ' . $title;
                $template = rootPath(config('app.templates_path')) . '/email/enabled_second_round.tpl';
    
                // 3) Envío mails solo a los pendientes
                $success = true;
                $oferentesPendientes = $concurso->oferentes()
                    ->where('etapa_actual', Participante::ETAPAS['economica-revisada'])
                    ->get();

                foreach ($oferentesPendientes as $oferente) {
                    $oferente->update([
                        'etapa_actual' => Participante::ETAPAS['economica-pendiente-' . $nuevaRonda]
                    ]);

                    $html = $this->fetch($template, [
                        'title'       => $title,
                        'ano'         => Carbon::now()->format('Y'),
                        'concurso'    => $concurso,
                        'company_name'=> $oferente->company->business_name,
                        'nuevaRonda'  => $rondaTitle,
                        'date_limit'  => Carbon::parse($dateNewRound)->format('d-m-Y H:i'),
                        'comentario'  => $body->CommentNewRound
                    ]);

                    $result = $emailService->send(
                        $html,
                        $subject,
                        $oferente->company->users->pluck('email'),
                        $oferente->company->business_name
                    );
                    $success = $success && $result['success'];
                }
    
                //Cleanup and error management
                if ($success) {
                    $connection->commit();
                    $message = 'Concurso habilitado para la ' . $rondaTitle . ' con éxito.';
                    $redirect_url = route('concursos.cliente.serveList');
                } else {
                    $connection->rollBack();
                    $message = 'Han ocurrido errores al enviar las notificaciones.';
                }
            }
    
        } catch (Exception $e) {
            if (isset($connection)) {
                $connection->rollBack();
            }
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

    public function getSeguimientoInvitaciones($concurso)
    {
        $result = [];
        foreach ($concurso->oferentes as $oferente) {
            $inv = $oferente->invitation;
            // status
            $statusDesc = $inv->status->description ?? null;

            // la fecha de respuesta SIEMPRE que no esté "pendiente"
            $fechaRespuesta = null;
            if ($inv->status_id !== 0) {
                $fechaRespuesta = $inv->updated_at
                    ? $inv->updated_at->format('d-m-Y H:i')
                    : null;
            }

            $result[] = [
                'IdOferente'             => $oferente->id_offerer,
                'IdConcurso'             => $oferente->id_concurso,
                'TipoConcursoPath'       => $concurso->tipo_concurso,
                'Nombre'                 => $oferente->company->business_name,
                'FechaConvocatoria'      => $inv->created_at
                                            ? $inv->created_at->format('d-m-Y H:i')
                                            : null,
                'FechaRecordatorio'      => $inv->reminder_date
                                            ? $inv->reminder_date->format('d-m-Y H:i')
                                            : null,
                'FechaAceptacionRechazo' => $fechaRespuesta,
                'HasInvitacionAceptada'  => $oferente->has_invitacion_aceptada,
                'IsInvitacionPendiente'  => $oferente->is_invitacion_pendiente,
                'IsInvitacionRechazada'  => $oferente->is_invitacion_rechazada,
                'Description'            => $statusDesc,
            ];
        }
        return $result;
    }


    public function getTechnicalEvaluations($concurso)
    {
        $oferenteProposalstech = [];
        $oferentes = $concurso->oferentes->where('has_invitacion_aceptada', true);
        $plantilla = $concurso->plantilla_tecnica ? $concurso->plantilla_tecnica->parsed_items : [];
        $cant_oferentes = count($oferentes);
        foreach ($oferentes as $index => $oferente) {
            $techRound = $oferente->ronda_tecnica;
            $rondasTech = [];
            for ($round = 1; $round <= $techRound; $round++) {
                $title = Participante::RONDAS[$round];
                $techProposal = $oferente->technicalByRound($round);
                $etapaPendding = $oferente->isTecnicaByRoundByStep($round, 'pendiente');
                $etapaPresented = !$etapaPendding && $techProposal ? true : false;
                $etapaDeclinated = $oferente->isTecnicaByRoundByStep($round, 'declinada');
                $etapaRejected = $oferente->isTecnicaByRoundByStep($round, 'rechazada');
                $documents = $techProposal ? $oferente->parsedTechnicalByRound($round) : [];
                $evaluation = new stdClass();
                if ($round == $techRound) {
                    $eval = $oferente->analisis_tecnica_valores ? $oferente->analisis_tecnica_valores[0] : null;
                    $approved = $eval ? (int) $eval['alcanzado'] >= (int) $eval['minimo'] : false;
                    $evaluation->atributo = $plantilla[0]->atributo;
                    $evaluation->puntaje = $plantilla[0]->puntaje;
                    $evaluation->valores = $eval ? explode(',', $eval['valores']) : [];
                    $evaluation->minimo = $eval ? $eval['minimo'] : null;
                    $evaluation->alcanzado = $eval ? $eval['alcanzado'] : null;
                    $evaluation->cssText = !$eval ? 'SIN CALIFICAR' : ($approved ? 'APROBADO' : 'REPROBADO');
                    $evaluation->comentario = !$eval ? null : ($approved ? null : $eval['comentario']);
                    $evaluation->cssColor = !$eval ? '#FF0000' : ($approved ? '#008000' : '#FF0000');
                    $evaluation->Evaluado = (bool) $eval;
                    $evaluation->oferente_id = $oferente->id;
                    $evaluation->razon_social = $oferente->company->business_name;
                    $evaluation->round = $techRound < 5 ? ($techRound + 1) : $techRound;
                    $evaluation->newRound = $techRound < 5 ? Participante::RONDAS[$techRound + 1] . ' Técnica' : Participante::RONDAS[$techRound];
                    $evaluation->lastRound = $techRound < 5 ? false : true;
                    $evaluation->plantilla = collect();
                    foreach ($plantilla as $key => $value) {
                        if ($key != 0) {
                            $evaluation->plantilla->push(clone $value);
                        }
                    }
                }

                $rondasTech[] = [
                    'title' => $title . ' técnica',
                    'tecnica_pendiente' => $etapaPendding,
                    'tecnica_presentada' => $etapaPresented,
                    'tecnica_declinada' => $etapaDeclinated,
                    'tecnica_rechazada' => $etapaRejected,
                    'tecnica_vencida' => $oferente->has_tecnica_vencida,
                    'comment' => $techProposal ? $techProposal->comment : null,
                    'documents' => $documents,
                    'evaluation' => $evaluation,
                    'activeRound' => $techRound == $round,
                    'refRound' => str_replace(' ', '', $title) . $oferente->id,
                    'proposal' => $techProposal ? $techProposal->id : null,
                    'oferente_id' => $oferente->id,
                    'revisada' => $techProposal ? $techProposal->is_revisada : null,
                    'comentario_nueva_roda' => $techProposal ? $techProposal->comentario_nueva_ronda : null,
                    'motivoDeclination' => $etapaDeclinated ? $oferente->reasonDeclination : null,
                    'fechaDeclinacion' => $etapaDeclinated ? $oferente->fecha_declination->format('d-m-Y') : null
                ];
            }


            $oferenteProposalstech[] = [
                'OferenteId' => $oferente->id,
                'razon_social' => $oferente->company->business_name,
                'rondasTecnicas' => $rondasTech,
                'activeOfferer' => $cant_oferentes == 1 ? true : ($cant_oferentes > 1 && $index == 0 ? true : false),
                'refOfferer' => $oferente->id
            ];
        }

        return $oferenteProposalstech;
    }

    public function getOfferersReputation($concurso)
    {
        $return = [];
        $oferentes = $concurso->oferentes->where('is_adjudicacion_aceptada', true);
        /*$oferentes = $concurso->oferentes
                    ->where('etapa_actual', Participante::ETAPAS['estrategia-aceptada']);*/

        foreach ($oferentes as $oferente) {
            $comentario = '';
            $permitirEnvio = true;
            if (isset($oferente->evaluacion)) {
                if (isset($oferente->evaluacion->comentario))
                    if (is_string($oferente->evaluacion->comentario))
                        $comentario = $oferente->evaluacion->comentario;

                if (isset($oferente->evaluacion->valores))
                    $permitirEnvio = false;
            }

            $evaluacion = [
                'Id' => $oferente->id_offerer,
                'RazonSocial' => $oferente->company->business_name,
                'Puntualidad' => '',
                'Calidad' => '',
                'OrdenYlimpieza' => '',
                'MedioAmbiente' => '',
                'HigieneYseguridad' => '',
                'Experiencia' => '',
                'Comentario' => $comentario,
                'PermitirEnvio' => $permitirEnvio
            ];
            if ($oferente->evaluacion) {
                if (!is_null($oferente->evaluacion->valores) && !empty($oferente->evaluacion->valores)) {
                    $valores = json_decode($oferente->evaluacion->valores, true);
                    $evaluacion = array_merge($evaluacion, $valores);
                }
            }
            $return[] = $evaluacion;
        }
        return $return;
    }

    private function setDates($numDias)
    {
        $fecha = date('Y-m-d', strtotime(date("Y-m-d") . " +$numDias day"));
        $isWeekend = (date('N', strtotime($fecha)) >= 6);
        $day = date('N', strtotime($fecha));
        switch ($day) {
            case 6:
                $sum = 2;
                break;
            case 7:
                $sum = 1;
                break;
            default:
                $sum = 0;
                break;
        }
        if ($isWeekend) {
            $final = $numDias + $sum;
            $fecha = date('Y-m-d', strtotime(date("Y-m-d") . " +$final day"));
        }

        return $fecha;
    }

    public static function GetTiposValoresOfertar()
    {
        return [
            [
                'id' => 'ascendente',
                'text' => 'Ascendente'
            ],
            [
                'id' => 'descendente',
                'text' => 'Descendente'
            ]
        ];
    }

    public static function GetPlantillasEconomicas()
    {
        return [
            [
                'id' => 1,
                'text' => 'Modelo 1'
            ],
            [
                'id' => 2,
                'text' => 'Modelo 2'
            ],
        ];
    }

    private function storePortrait($old_image, $entity)
    {
        $portait = $entity->Portrait;
        $absolute_path = filePath(config('app.images_path'), true);
        if (isset($portait->action))
            switch ($portait->action) {
                case 'upload':
                case 'clear':
                case 'delete':
                    if (!empty($absolute_path) && !empty($old_image)) {
                        if ($old_image) {
                            @unlink($absolute_path . $old_image);
                        }
                    }
                    break;
                default:
                    break;
            }
    }

    private function storePortraitDescription($old_image, $entity)
    {
        $portait = $entity->DescripcionPortrait;
        $absolute_path = filePath(config('app.images_path'), true);
        if (isset($portait->action))
            switch ($portait->action) {
                case 'upload':
                case 'clear':
                case 'delete':
                    if (!empty($absolute_path) && !empty($old_image)) {
                        if ($old_image) {
                            @unlink($absolute_path . $old_image);
                        }
                    }
                    break;
                default:
                    break;
            }
    }

    private function storeSheets($concurso, $entity)
    {
        $absolute_path = filePath($concurso->file_path, true);
        $documentChange = false;
        $documentDeleted = false;
        // Pliegos
        foreach ($entity->Sheets as $sheet) {
            switch ($sheet->action) {
                case 'upload':
                    // Si había un archivo previo, lo eliminamos.
                    if ($sheet->id) {
                        $to_delete = Sheet::find($sheet->id);
                        @unlink($absolute_path . $to_delete->filename);
                        $to_delete->delete();
                    }

                    // Guardamos el nuevo archivo
                    $new_sheet = new Sheet([
                        'concurso_id' => $concurso->id,
                        'type_id' => (int) $sheet->type_id,
                        'filename' => $sheet->filename
                    ]);
                    $new_sheet->save();
                    $documentChange = true;
                    break;
                case 'clear':
                case 'delete':
                    // Si el archivo ya estaba guardado
                    if ($sheet->id) {
                        $to_delete = Sheet::find($sheet->id);
                        @unlink($absolute_path . $to_delete->filename);
                        $to_delete->delete();
                    }
                    $documentDeleted = true;
                    break;
                default:
                    break;
            }
        }

        return [
            'documentChange' => $documentChange,
            'documentDeleted' => $documentDeleted
        ];
    }

    public function checkProducts(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;


        try {
            $body = json_decode($request->getParsedBody()['Data']);


            $fields = [
                'products' => []
            ];

            foreach ($body->Products as $product) {
                $fields['products'][] = [
                    'nombre' => $product->name,
                    'cantidad' => (float) $product->quantity,
                    'oferta_minima' => (float) $product->minimum_quantity,
                    'unidad' => (int) $product->measurement_id
                ];
            }

            $validator = $this->validateProducts($body, $fields, $body->IsSobrecerrado, $body->IsOnline);

            if ($validator->fails()) {
                $sucess = false;
                $message = $validator->errors()->first();
                $status = 422;
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

    private function storeProducts($concurso, $body)
    {

        try {
            $results = [
                'success' => false,
                'message' => null,
                'productsDeleted' => [],
                'productsUpdated' => [],
                'productsNew' => [],
            ];

            $user = user();

            $fields = [
                'products' => []
            ];


            foreach ($body->Products as $product) {
                $fields['products'][] = [
                    'id' => isset($product->id) ? $product->id : null,
                    'id_usuario' => isset($user->id) ? $user->id : null,
                    'id_concurso' => $concurso->id,
                    'nombre' => $product->name,
                    'descripcion' => isset($product->description) ? $product->description : null,
                    'cantidad' => (float) $product->quantity,
                    'oferta_minima' => (float) $product->minimum_quantity,
                    'unidad' => $product->measurement_id,
                    'targetcost' => isset($product->targetcost) ? $product->targetcost : 0
                ];
            }

            $validator = $this->validateProducts($body, $fields, $concurso->is_sobrecerrado, $concurso->is_online);

            if ($validator->fails()) {
                $results['success'] = false;
                $results['message'] = $validator->errors()->first();
            } else {
                // Delete
                foreach ($concurso->productos as $product) {
                    if (array_search($product->id, array_column($fields['products'], 'id')) === false) {
                        $product->delete();
                        $results['productsDeleted'][] = $product;
                    }
                }
                $concurso->refresh();

                // Update or Create
                foreach ($fields['products'] as $product) {
                    if ($product['id']) {
                        $existent_product = $concurso->productos->find($product['id']);
                        if ($existent_product != null) {
                            if ($product['nombre'] != $existent_product->nombre || $product['cantidad'] != $existent_product->cantidad || $product['oferta_minima'] != $existent_product->oferta_minima || $product['unidad'] != $existent_product->unidad || $product['targetcost'] != $existent_product->targetcost) {
                                $results['productsUpdated'][] = $product;
                            }
                            $existent_product->update($product);
                        }
                    } else {
                        $new_product = new Producto($product);
                        $new_product->save();
                        $results['productsNew'][] = $product;
                    }
                    $results['success'] = true;
                }

            }
        } catch (Exception $e) {
            $results['success'] = false;
            $results['message'] = $e->getMessage();
        }

        return $results;
    }

    private function storePayroll($concurso, $body, $createOrEdit)
    {
        
        if ($createOrEdit === 'create') {
            if ($body->IncluyePrecalifTecnica === 'no') {
                return [
                    'success' => true,
                    'message' => 'No incluye Etapa Técnica',
                    'technicalAdded' => false,
                    'technicalDeleted' => false,
                    'technicalChanged' => false
                ];
            }

            if ($body->IncluyePrecalifTecnica === 'si') {
                $plantilla_tecnica = [
                    'payroll' => []
                ];
                $plantilla_tecnica = array_merge($plantilla_tecnica, [
                    'puntaje_minimo' => $body->PlantillaTecnicaSeleccionada->puntaje_minimo,
                    'total' => $body->PlantillaTecnicaSeleccionada->total
                ]);

                foreach ($body->PlantillaTecnicaSeleccionada->payroll as $payroll_item) {
                    if ((int) $payroll_item->ponderacion > 0) {
                        $plantilla_tecnica['payroll'][] = [
                            'id' => $payroll_item->id,
                            'atributo' => $payroll_item->atributo,
                            'ponderacion' => $payroll_item->ponderacion,
                            'puntaje' => $payroll_item->puntaje,
                            'id_plantilla' => $payroll_item->id_plantilla
                        ];
                    }
                }

                $validator = $this->validatePayroll($plantilla_tecnica);

                if ($validator->fails()) {
                    return [
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'technicalAdded' => false,
                        'technicalDeleted' => false,
                        'technicalChanged' => false
                    ];
                }

                $atributos = [];
                $atributos[] = [
                    'id' => 0,
                    'atributo' => 'Puntaje mínimo necesario',
                    'puntaje' => $body->PlantillaTecnicaSeleccionada->puntaje_minimo,
                    'ponderacion' => 0
                ];
                $atributos = array_merge($atributos, $plantilla_tecnica['payroll']);

                $plantillaTecnica = new PlantillaTecnica([
                    'atributos' => json_encode($atributos)
                ]);

                $plantillaTecnica->concurso()->associate($concurso);
                $plantillaTecnica->save();

                return [
                    'success' => true,
                    'message' => 'Agregada Plantilla Técnica',
                    'technicalAdded' => true,
                    'technicalDeleted' => false,
                    'technicalChanged' => false
                ];
            }

        }

        if ($createOrEdit === 'edit') {
            if ($body->IncluyePrecalifTecnica === 'no') {
                if ($concurso->plantilla_tecnica) {
                    $concurso->plantilla_tecnica->delete();
                    return [
                        'success' => true,
                        'message' => 'Se ha eliminado la precalificación técnica',
                        'technicalAdded' => false,
                        'technicalDeleted' => true,
                        'technicalChanged' => false
                    ];
                }

                if (!$concurso->plantilla_tecnica) {
                    return [
                        'success' => true,
                        'message' => 'No incluye Etapa Técnica',
                        'technicalAdded' => false,
                        'technicalDeleted' => false,
                        'technicalChanged' => false
                    ];
                }

            }

            if ($body->IncluyePrecalifTecnica === 'si') {
                $plantilla_tecnica = [
                    'payroll' => []
                ];
                $plantilla_tecnica = array_merge($plantilla_tecnica, [
                    'puntaje_minimo' => $body->PlantillaTecnicaSeleccionada->puntaje_minimo,
                    'total' => $body->PlantillaTecnicaSeleccionada->total
                ]);

                foreach ($body->PlantillaTecnicaSeleccionada->payroll as $payroll_item) {
                    if ((int) $payroll_item->ponderacion > 0) {
                        $plantilla_tecnica['payroll'][] = [
                            'id' => $payroll_item->id,
                            'atributo' => $payroll_item->atributo,
                            'ponderacion' => $payroll_item->ponderacion,
                            'puntaje' => $payroll_item->puntaje,
                            'id_plantilla' => $payroll_item->id_plantilla
                        ];
                    }
                }

                $validator = $this->validatePayroll($plantilla_tecnica);

                if ($validator->fails()) {
                    return [
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'technicalAdded' => false,
                        'technicalDeleted' => false,
                        'technicalChanged' => false
                    ];
                }

                $atributos = [];
                $atributos[] = [
                    'id' => 0,
                    'atributo' => 'Puntaje mínimo necesario',
                    'puntaje' => $body->PlantillaTecnicaSeleccionada->puntaje_minimo,
                    'ponderacion' => 0
                ];
                $atributos = array_merge($atributos, $plantilla_tecnica['payroll']);

                if (!$concurso->plantilla_tecnica) {
                    $plantillaTecnica = new PlantillaTecnica([
                        'atributos' => json_encode($atributos)
                    ]);

                    $plantillaTecnica->concurso()->associate($concurso);
                    $plantillaTecnica->save();

                    return [
                        'success' => true,
                        'message' => 'Agregada Plantilla Técnica',
                        'technicalAdded' => true,
                        'technicalDeleted' => false,
                        'technicalChanged' => false
                    ];
                }

                if ($concurso->plantilla_tecnica) {
                    $newPayRoll = json_decode(json_encode($atributos));
                    $oldPayroll = json_decode($concurso->plantilla_tecnica->atributos);
                    $success = true;
                    $message = 'Sin cambios';
                    $technicalChanged = false;

                    if ($oldPayroll[0]->puntaje != $newPayRoll[0]->puntaje) {
                        $success = true;
                        $message = 'Se ha Cambiado la precalificación técnica';
                        $technicalChanged = true;
                    }
                    
                    if(count($newPayRoll) != count($oldPayroll)){
                        $success = true;
                        $message = 'Se ha Cambiado la precalificación técnica';
                        $technicalChanged = true;
                    }else{
                        foreach ($newPayRoll as $key => $payroll) {
                            if ($key === 0)
                                continue;
                            if (
                                $oldPayroll[$key]->id != $payroll->id ||
                                $oldPayroll[$key]->ponderacion != $payroll->ponderacion ||
                                $oldPayroll[$key]->id_plantilla != $payroll->id_plantilla
                            ) {
                                $success = true;
                                $message = 'Se ha Cambiado la precalificación técnica';
                                $technicalChanged = true;
                            }
                        }
                    }                    
                    $concurso->plantilla_tecnica->delete();
                    $plantillaTecnica = new PlantillaTecnica([
                        'atributos' => json_encode($atributos)
                    ]);
                    
                    $plantillaTecnica->concurso()->associate($concurso);
                    $plantillaTecnica->save();

                    return [
                        'success' => $success,
                        'message' => $message,
                        'technicalAdded' => false,
                        'technicalDeleted' => false,
                        'technicalChanged' => $technicalChanged
                    ];
                }
            }

        }
    }

    private function storeParticipantes($concurso, $entity)
    {
        // Eliminamos los que todavía estén en estado seleccionado (no se enviaron invitaciones)
        $concurso->refresh();
        foreach ($concurso->oferentes->where('is_seleccionado', true) as $oferente) {
            $oferente->delete();
        }

        // Creamos los nuevos
        $concurso->refresh();

        if (count($entity->OferentesAInvitar) > 0) {
            foreach ($entity->OferentesAInvitar as $seleccionado) {
                $oferente = $concurso->oferentes->where('id_offerer', $seleccionado)->first();
                if ($oferente) {
                    continue;
                }

                $oferente = new Participante([
                    'id_offerer' => $seleccionado,
                    'id_concurso' => $concurso->id,
                    'etapa_actual' => Participante::ETAPAS['seleccionado']
                ]);
                $oferente->save();
            }
            $concurso->refresh();
            return true;
        }

        return false;
    }

    private function mapDocuments($entity)
    {
        $go_documents = collect();
        // Documentos GCG: Conductor
        foreach ($entity->DriverDocumentsSelected as $selected_document) {

            $go_document = new GoDocument([
                'id_document' => $selected_document,
            ]);

            // Campos para Accidentes Personales.
            if (Document::find($selected_document)->gcg_code === 'OPTUS_CUOTA AP') {
                $go_document->id_policy_amount = $entity->AmountSelect;
            }

            $go_documents = $go_documents->push($go_document);
        }

        // Documentos GCG: Vehículo
        foreach ($entity->VehicleDocumentsSelected as $selected_document) {

            $go_document = new GoDocument([
                'id_document' => $selected_document,
            ]);

            $go_documents = $go_documents->push($go_document);
        }
        // Documentos NO-GCG
        if ($entity->ClausulaArt) {
            $go_document = new GoDocument([
                'id_document' => Document::where('gcg_code', 'NOGCG_NO_REP_ART')->first()->id,
                'cuit' => $entity->CuitDoc,
                'razon_social' => $entity->RazonSocialDoc
            ]);
            $go_documents = $go_documents->push($go_document);
        }

        if ($entity->ClausulaBeneficiario) {
            $go_document = new GoDocument([
                'id_document' => Document::where('gcg_code', 'NOGCG_BENEF')->first()->id,
                'cuit' => $entity->CuitBeneficiario,
                'razon_social' => $entity->RazonSocialBeneficiario,
            ]);
            $go_documents = $go_documents->push($go_document);
        }

        return $go_documents;
    }

    private function mapAdditionalDocuments($entity)
    {
        $go_additional_documents = collect();

        // Documentos Adicionales Conductor
        foreach ($entity->AdditionalDriverDocuments as $selected_document) {
            $go_additional_document = new GoDocumentAdditional([
                'name' => $selected_document,
                'type' => GoDocumentAdditional::TYPE_SLUGS['driver']
            ]);
            $go_additional_documents = $go_additional_documents->push($go_additional_document);
        }

        // Documentos Adicionales Vehículo
        foreach ($entity->AdditionalVehicleDocuments as $selected_document) {
            $go_additional_document = new GoDocumentAdditional([
                'name' => $selected_document,
                'type' => GoDocumentAdditional::TYPE_SLUGS['vehicle']
            ]);
            $go_additional_documents = $go_additional_documents->push($go_additional_document);
        }
        return $go_additional_documents;
    }

    private function validate($body, $fields, $is_sobrecerrado, $is_online, $is_go, $create)
    {
        // $txt = fopen('1.txt','w');
        // fwrite($txt, json_encode($body));
        // fclose($txt);
        $conditional_rules = [];
        date_default_timezone_set(user()->customer_company->timeZone);

        // COMMON
        $common_rules = [
            'nombre' => 'required',
            'area_sol' => 'required',
            'tipo_operacion' => 'required|exists:concursos_tipo_operaciones,id',
            'tipo_convocatoria' => 'required|exists:tipo_convocatoria,id',
            'fecha_limite' => 'required|date_format:Y-m-d H:i:s' . ($create ? ('|after_or_equal:' . Carbon::now()->addDays(1)->format('Y-m-d H:i:s')) : ''),
            'moneda' => 'required|exists:monedas,id',
            'finalizacion_consultas' => [
                'required',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) use ($body) {
                    $fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimite);
                    $fecha_limite_economicas = isset($body->FechaLimiteEconomicas) ? Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimiteEconomicas)->format('Y-m-d H:i:s') : null;
                    $finalizacion_consultas = Carbon::createFromFormat('Y-m-d H:i:s', $value);
                    if ($fecha_limite->diffInDays($finalizacion_consultas, false) < 1)
                        $fail('La fecha de Muro de Consultas debe ser al menos 1 días mayor a la fecha de Aceptación.');
/*
                    if ($fecha_limite_economicas != null)
                        if ($finalizacion_consultas->diffInDays($fecha_limite_economicas, false) < 1)
                            $fail('La fecha de Muro de Consultas debe ser al menos 1 día menor a la fecha de Presentación Económica.');
*/
                }
            ]
        ];

        // ONLINE
        if ($is_online) {
            $precio_inferior = $fields['precio_minimo'] > $fields['unidad_minima'] ? $fields['precio_minimo'] : $fields['unidad_minima'];
            $conditional_rules = array_merge($conditional_rules, [
                'tipo_valor_ofertar' => 'required|in:ascendente,descendente',
                'duracion' => 'required|numeric|min:60',
                'tiempo_adicional' => 'required|numeric|between:1,120',
                'precio_minimo' => 'required|numeric|min:1',
                'unidad_minima' => 'required|numeric|min:1',
                'precio_maximo' => 'required|numeric|min:' . (($precio_inferior ? $precio_inferior : 0) * 10),
                'inicio_subasta' => [
                    'required',
                    'date_format:Y-m-d H:i:s',
                    function ($attribute, $value, $fail) use ($body) {
                        $inicio_subasta = Carbon::createFromFormat('Y-m-d H:i:s', $value);
                        $finalizacion_consultas = isset($body->finalizacion_consultas) ? Carbon::createFromFormat('d-m-Y H:i', $body->finalizacion_consultas)->format('Y-m-d H:i:s') : null;
                        if ($body->IncluyePrecalifTecnica == 'si') {
                            if ($body->FechaLimiteTecnica) {
                                $ficha_tecnica_fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimiteTecnica);
                                if ($ficha_tecnica_fecha_limite->diffInDays($inicio_subasta, false) < 1)
                                    $fail('La fecha de Inicio de Concurso debe ser al menos 1 día mayor a la de Presentación Técnica.');
                            }
                        } else {
                            $fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimite);
                            if ($fecha_limite->diffInDays($inicio_subasta, false) < 1)
                                $fail('La fecha de Inicio de Concurso debe ser al menos 1 día mayor a la fecha de Aceptación.');
                        }
                        if ($finalizacion_consultas != null)
                            if ($finalizacion_consultas->diffInDays($inicio_subasta, false) < 1)
                                $fail('La fecha de Inicio de Concurso debe ser al menos 1 día mayor a la fecha de Muro de Consultas.');
                    },
                ]
            ]);
        }

        // GO
        if ($is_go) {
            $conditional_rules = array_merge($conditional_rules, [
                'type_id' => 'required|exists:go_types,id',
                'load_type_id' => 'required|exists:go_load_types,id',
                'peso' => 'required|integer|gte:0|lte:70000',
                'ancho' => 'required|numeric|gte:1|lte:3',
                'largo' => 'required|numeric|gte:0|lte:22',
                'alto' => 'required|numeric|gte:0|lte:6',
                'unidades_bultos' => 'required|integer|gte:0|lte:500',
                'payment_method_id' => 'required|exists:go_payment_methods,id',
                'plazo_pago' => 'required|numeric',
                'fecha_desde' => 'required|date_format:Y-m-d H:i:s|after_or_equal:fecha_alta',
                'fecha_hasta' => 'required|date_format:Y-m-d H:i:s|after_or_equal:fecha_desde',
                'calle_desde' => 'required|string|max:200',
                'calle_hasta' => 'required|string|max:200',
                'numeracion_desde' => 'required|string|max:5',
                'numeracion_hasta' => 'required|string|max:5',
                'ciudad_desde_id' => 'required|exists:ciudades,id',
                'ciudad_hasta_id' => 'required|exists:ciudades,id',
                'provincia_desde_id' => 'required|exists:provincias,id',
                'provincia_hasta_id' => 'required|exists:provincias,id',
                'fecha_limite_economicas' => [
                    'required',
                    'date_format:Y-m-d H:i:s',
                    function ($attribute, $value, $fail) use ($body) {
                        $fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimite);
                        $fecha_limite_economicas = Carbon::createFromFormat('Y-m-d H:i:s', $value);
                        if ($fecha_limite->diffInDays($fecha_limite_economicas, false) < 3) {
                            $fail('La fecha de Presentación Económica debe ser al menos 3 días posterior a la fecha de Aceptación.');
                        }
                    },
                ]
            ]);
        }

        // SOBRECERRADO AND ONLINE
        if ($is_sobrecerrado || $is_online) {
            $conditional_rules = array_merge($conditional_rules, [
                //'pais' => 'required',
                //'provincia' => 'required',
                //'localidad' => 'required',
                //'direccion' => 'required',
            ]);

            if ($body->IncluyePrecalifTecnica == 'si') {
                $conditional_rules = array_merge($conditional_rules, [
                    'ficha_tecnica_plantilla' => [
                        'required',
                        'exists:plantilla_precalificacion_tecnica,id'
                    ],
                    'ficha_tecnica_fecha_limite' => [
                        'required',
                        'date_format:Y-m-d H:i:s',
                        function ($attribute, $value, $fail) use ($body) {
                            if ($value) {
                                $fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimite);
                                $fecha_limite_economicas = isset($body->FechaLimiteEconomicas) ? Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimiteEconomicas)->format('Y-m-d H:i:s') : null;
                                $ficha_tecnica_fecha_limite = Carbon::createFromFormat('Y-m-d H:i:s', $value);

                                if ($fecha_limite->diffInDays($ficha_tecnica_fecha_limite, false) < 2)
                                    $fail('La fecha de Presentación Técnica debe ser al menos 2 días mayor a la fecha de Aceptación.');

                                if ($fecha_limite_economicas != null)
                                    if ($ficha_tecnica_fecha_limite->diffInDays($fecha_limite_economicas, false) < 1)
                                        $fail('La fecha de Presentación Técnica debe ser al menos 1 día menor a la fecha de Presentación Económica.');
                            }
                        }
                    ]
                ]);
            }
        }

        // SOBRECERRADO
        if ($is_sobrecerrado) {
            $conditional_rules = array_merge($conditional_rules, [
                'fecha_limite_economicas' => [
                    'required',
                    'date_format:Y-m-d H:i:s',
                    function ($attribute, $value, $fail) use ($body) {

                        $finalizacion_consultas = Carbon::createFromFormat('d-m-Y H:i', $body->FinalizacionConsultas);
                        $fecha_limite_economicas = Carbon::createFromFormat('Y-m-d H:i:s', $value);

                        if ($body->IncluyePrecalifTecnica == 'si') {
                            if ($body->FechaLimiteTecnica) {
                                $ficha_tecnica_fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimiteTecnica);
                                if ($finalizacion_consultas->greaterThan($ficha_tecnica_fecha_limite)) {
                                    if ($finalizacion_consultas->diffInDays($fecha_limite_economicas, false) < 1)
                                        $fail('La fecha de Presentación Económica debe ser al menos 1 día mayor a la fecha de Muro de Consulta.');
                                } else {
                                    if ($ficha_tecnica_fecha_limite->diffInDays($fecha_limite_economicas, false) < 1)
                                        $fail('La fecha de Presentación Económica debe ser al menos 1 día mayor a la de Presentación Técnica.');
                                }
                            }
                        } else {
                            $fecha_limite = Carbon::createFromFormat('d-m-Y H:i', $body->FechaLimite);
                            // if ($finalizacion_consultas->greaterThan($fecha_limite)) {
                            if ($finalizacion_consultas->diffInDays($fecha_limite_economicas, false) < 1)
                                $fail('La fecha de Presentación Económica debe ser al menos 1 día mayor a la fecha de Muro de Consulta.');
                            if ($fecha_limite->diffInDays($fecha_limite_economicas, false) < 3)
                                    $fail('La fecha de Presentación Económica debe ser al menos 3 días mayor a la fecha para aceptar la invitación.');
                        }
                    },
                ]
            ]);
        }

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules)
        );
    }

    private function validatePayroll($fields)
    {
        $conditional_rules = [];

        // COMMON
        $common_rules = [
            'puntaje_minimo' => [
                'required',
                'numeric',
                'in:0,10,20,30,40,50,60,70,80,90,100'
            ],
            'total' => [
                'required',
                'numeric',
                'in:100'
            ],
            'payroll.*.ponderacion' => [
                'required',
                'numeric',
                'gte:0',
                'lte:100'
            ],
            'payroll.*.atributo' => [
                'required',
                'string'
            ],
            'payroll.*.puntaje' => [
                'required',
                'string'
            ],
            'payroll.*.id_plantilla' => [
                'required',
                'exists:plantilla_precalificacion_tecnica,id'
            ]
        ];

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules),
            $messages = [
                'total.in' => 'Los valores de ponderación deben sumar 100% (valor actual ' . $fields['total'] . '%)',
                
            ]
        );
    }

    private function validateProducts($body, $fields, $is_sobrecerrado, $is_online)
    {
        $conditional_rules = [];

        // COMMON
        $common_rules = [
            'products' => [
                'required'
            ],
            'products.*' => [
                'required'
            ],
            'products.*.nombre' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'distinct'
            ],
            'products.*.cantidad' => [
                'required',
                'numeric',
                'gt:0',
                'lte:99000000'
            ],
            'products.*.unidad' => [
                'required',
                'exists:measurements,id'
            ]
        ];

        if ($is_sobrecerrado || $is_online) {
            $conditional_rules = array_merge($conditional_rules, [
                'products.*.oferta_minima' => 'required|numeric|gt:0|lte:products.*.cantidad',
            ]);
        }

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules),
            $messages = [
                'products.*.nombre.max' => 'El campo Nombre Item no puede ser superior a 255 carácteres',
            ]
            
        );
    }

    public function verOfertas(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $redirect_url = null;

        try {
            if(isset($request->getParsedBody()['Token'])){
                $token = $request->getParsedBody()['Token'];
            }
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $user = user();
            $concurso = $user->customer_company->getAllConcursosByCompany()->find($params['id']);
        
            if($concurso->concurso_fiscalizado == 'si' && $concurso->token != $token){
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Token no Valido',
                ], 422);
            }
            $offerers = $concurso->oferentes_etapa_economica->where('has_economica_presentada', true);
            foreach ($offerers as $offerer) {
                $offerer->update([
                    'etapa_actual' => Participante::ETAPAS['economica-revisada']
                ]);
                $offerer->refresh();
            }
            $connection->commit();
            $message = 'Ofertas actualizadas.';
            $success = true;
        } catch (Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        try {
            return $this->json($response, [
                'success' => $success,
                'message' => $message,
                'data' => [
                    'redirect' => $redirect_url
                ]
            ], $status);
        } catch (Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function createConcurso($type, $entity)
    {
        $concurso = null;
        $is_go = $type == Concurso::TYPES['go'];
        $is_sobrecerrado = $type == Concurso::TYPES['sobrecerrado'];
        $is_online = $type == Concurso::TYPES['online'];
        $old_image = null;
        $old_image_descripcion = null;

        $gos = [];

        $entity->IsOnline = $is_online;
        $entity->IsSobrecerrado = $is_sobrecerrado;

        $extra_fields = [];
        // COMMON
        $common_fields = [
            'id_cliente' => user()->id,
            'tipo_concurso' => $entity->Tipo,
            'tipo_operacion' => 2,
            'nombre' => $entity->Nombre,
            'area_sol' => $entity->AreaUsr,
            'fecha_alta' => $entity->FechaAlta,
            'resena' => $entity->Resena,
            'descripcion' => null,
            'pais' => $entity->Pais,
            'provincia' => $entity->Provincia,
            'localidad' => $entity->Localidad,
            'direccion' => $entity->Direccion,
            'cp' => $entity->Cp,
            'latitud' => $entity->Latitud,
            'longitud' => $entity->Longitud,
            'tipo_convocatoria' => $entity->TipoConvocatoria,
            'finalizacion_consultas' =>
                $entity->FinalizacionConsultas ?
                Carbon::createFromFormat('d-m-Y H:i', $entity->FinalizacionConsultas)->format('Y-m-d H:i:s') :
                null,
            'aceptacion_terminos' => $entity->AceptacionTerminos,
            'aperturasobre' => $entity->Aperturasobre,
            'fecha_limite' =>
                $entity->FechaLimite ?
                Carbon::createFromFormat('d-m-Y H:i', $entity->FechaLimite)->format('Y-m-d H:i:s') :
                null,
            'seguro_caucion' => $entity->SeguroCaucion,
            
            'lista_prov' => $entity->ListaProveedores,
            'cert_visita' => $entity->CertificadoVisitaObra,

            'diagrama_gant' => $entity->DiagramaGant,
            'base_condiciones_firmado' => $entity->BaseCondicionesFirmado,
            'condiciones_generales' => $entity->CondicionesGenerales,
            'pliego_tecnico' => $entity->PliegoTecnico,
            'acuerdo_confidencialidad' => $entity->AcuerdoConfidencialidad,
            'legajo_impositivo' => $entity->LegajoImpositivo,
            'antecendentes_referencia' => $entity->AntecedentesReferencias,
            'reporte_accidentes' => $entity->ReporteAccidentes,
            'estructura_costos' => $entity->EstructuraCostos,
            'apu' => $entity->Apu,
            'usuario_califica_reputacion' => isset($entity->UsuarioCalificaReputacion) ? implode(',', $entity->UsuarioCalificaReputacion) : null,
            'moneda' => $entity->Moneda,
            'ficha_tecnica_incluye' => $entity->IncluyePrecalifTecnica,
            'descriptionTitle' => $entity->DescripcionTitle,
            'descriptionDescription' => $entity->DescripcionDescription,
            'descriptionUrl' => $entity->DescripcionURL,
            'tecnico_ofertas' => $entity->TecnicoOfertas,
            'condicion_pago' => $entity->CondicionPago,
            'nom251' => $entity->nom251,
            'distintivo' => $entity->distintivo,
            'filtros_sanitarios' => $entity->filtros_sanitarios,
            'repse' => $entity->repse,
            'poliza' => $entity->poliza,
            'primariesgo' => $entity->primariesgo,
            'obras_referencias' => $entity->obras_referencias,
            'obras_organigrama' => $entity->obras_organigrama,
            'obras_equipos' => $entity->obras_equipos,
            'obras_cronograma' => $entity->obras_cronograma,
            'obras_memoria' => $entity->obras_memoria,
            'obras_antecedentes' => $entity->obras_antecedentes,
            'tarima_ficha_tecnica' => $entity->tarima_ficha_tecnica,
            'tarima_licencia' => $entity->tarima_licencia,
            'tarima_nom_144' => $entity->tarima_nom_144,
            'tarima_acreditacion' => $entity->tarima_acreditacion,
            'concurso_fiscalizado' => $entity->concurso_fiscalizado,
            'edificio_balance' => $entity->edificio_balance,
            'edificio_iva' => $entity->edificio_iva,
            'edificio_cuit' => $entity->edificio_cuit,
            'edificio_brochure' => $entity->edificio_brochure,
            'edificio_organigrama' => $entity->edificio_organigrama,
            'edificio_organigrama_obra' => $entity->edificio_organigrama_obra,
            'edificio_subcontratistas' => $entity->edificio_subcontratistas,
            'edificio_gestion' => $entity->edificio_gestion,
            'edificio_maquinas' => $entity->edificio_maquinas,
            'usuario_fiscalizador' => $entity->UsuarioSupervisor,
        ];

        // ONLINE
        if ($is_online || $is_sobrecerrado) {
            $old_image = null;
            $old_image_descripcion = null;
        }

        if ($is_online)
            $extra_fields = $this->storeAuction($entity, $extra_fields);

        if ($is_sobrecerrado)
            $extra_fields = $this->storeBidding($entity, $extra_fields);

        if ($is_go) {
            $gos = [
                'type_id' => GoType::all()->first()->id,
                'load_type_id' => $entity->GoLoadType,
                'peso' => $entity->Peso,
                'ancho' => $entity->Ancho,
                'largo' => $entity->Largo,
                'alto' => $entity->Alto,
                'unidades_bultos' => $entity->UnidadesBultos,
                'payment_method_id' => $entity->PaymentMethod,
                'plazo_pago' => $entity->PlazoPago,
                'fecha_desde' =>
                    $entity->FechaDesde ?
                    Carbon::createFromFormat('d-m-Y H:i', $entity->FechaDesde)->format('Y-m-d H:i:s') :
                    null,
                'fecha_hasta' =>
                    $entity->FechaHasta ?
                    Carbon::createFromFormat('d-m-Y H:i', $entity->FechaHasta)->format('Y-m-d H:i:s') :
                    null,
                'calle_desde' => $entity->CalleDesde,
                'calle_hasta' => $entity->CalleHasta,
                'numeracion_desde' => $entity->NumeracionDesde,
                'numeracion_hasta' => $entity->NumeracionHasta,
                'ciudad_desde_id' => $entity->CiudadDesdeSelect,
                'ciudad_hasta_id' => $entity->CiudadHastaSelect,
                'provincia_desde_id' => $entity->ProvinciaDesdeSelect,
                'provincia_hasta_id' => $entity->ProvinciaHastaSelect,
                'nombre_desde' => $entity->NombreDesde,
                'nombre_hasta' => $entity->NombreHasta,
                'cotizar_seguro' => $entity->CotizarSeguro,
                'suma_asegurada' => $entity->SumaAsegurada,
                'cotizar_armada' => $entity->CotizarArmada,
            ];
            $go_documents = $this->mapDocuments($entity);
            $go_additional_documents = $this->mapAdditionalDocuments($entity);
            $extra_fields = $this->storeGO($entity, $extra_fields);
        }

        // Validar
        $fields = array_merge($common_fields, $extra_fields, $gos);

        $validator = $this->validate(
            $entity,
            $fields,
            $is_sobrecerrado,
            $is_online,
            $is_go,
            true
        );

        if ($validator->fails()) {
            return [
                'success' => false,
                'error' => true,
                'message' => $validator->errors()->first(),
                'status' => 422
            ];
        }

        $concurso = new Concurso(array_merge($common_fields, $extra_fields));

        $go = $is_go ? new Go($gos) : null;
        if ($is_go) {
            // GO
            $go->save();
            $concurso->id_go = $go->id;
        }
        // Guardar concurso
        $concurso->save();
        $concurso->refresh();

        // GO
        if ($is_go) {
            // Documentos GCG/NO-GCG
            foreach ($go_documents as $go_document) {
                $go_document->id_go = $go->id;
                $go_document->save();
            }

            // Documentos Adicionales
            foreach ($go_additional_documents as $additional_document) {
                $additional_document->id_go = $go->id;
                $additional_document->save();
            }
        }

        // SOBRECERRADO Y ONLINE
        if ($is_sobrecerrado || $is_online) {
            // Portada
            $this->storePortrait($old_image, $entity);
            $this->storePortraitDescription($old_image_descripcion, $entity);
        }

        $documentsChanged = $this->storeSheets($concurso, $entity);

        // Productos
        $products_results = $this->storeProducts($concurso, $entity);

        if (!$products_results['success']) {
            return [
                'success' => false,
                'error' => true,
                'message' => $products_results['message'],
                'status' => 422
            ];
        }

        // Plantilla Técnica
        $payroll_results = $this->storePayroll($concurso, $entity, 'create');

        if (!$payroll_results['success']) {
            return [
                'success' => false,
                'error' => true,
                'message' => $payroll_results['message'],
                'status' => 422
            ];
        }

        // Oferentes
        if (!$this->storeParticipantes($concurso, $entity)) {
            return [
                'success' => false,
                'error' => true,
                'message' => 'Debe invitar al menos un oferente al concurso.',
                'status' => 422
            ];
        } else {
            $concurso->refresh();
        }

        return [
            'success' => true,
            'error' => false,
            'data' => [
                'concurso' => $concurso,
                'ajustdate' => false,
                'documentsChanged' => $documentsChanged,
                'products_results' => $products_results,
                'payroll_results' => $payroll_results
            ],
            'status' => 200
        ];
    }

    private function editConcurso($tipo, $concurso_id, $entity)
    {
        $concurso = Concurso::find($concurso_id);

        // dd($entity);

        $extra_fields = [];
        $gos = [];
        $old_image = null;
        $old_image_descripcion = null;
        $ajustdate = false;
        $documentsChanged = false;
        $ajustDocumentsTecnical = [];
        $ajustDocumentsEconomica = [];
        $payroll_results = null;

        // COMMON
        $common_fields = [
            //'id_cliente' => user()->id,
            'tipo_concurso' => $concurso->tipo_concurso,
            'tipo_operacion' => $concurso->tipo_operacion,
            'nombre' => $entity->Nombre,
            'area_sol' => $entity->AreaUsr,
            'fecha_alta' => $entity->FechaAlta,
            'resena' => $entity->Resena,
            'pais' => $entity->Pais,
            'provincia' => $entity->Provincia,
            'localidad' => $entity->Localidad,
            'direccion' => $entity->Direccion,
            'cp' => $entity->Cp,
            'latitud' => $entity->Latitud,
            'longitud' => $entity->Longitud,
            'tipo_convocatoria' => $entity->TipoConvocatoria,
            'aceptacion_terminos' => $entity->AceptacionTerminos,
            'aperturasobre' => $entity->Aperturasobre,
            'finalizacion_consultas' => $this->formatDates($entity->FinalizacionConsultas),
            'fecha_limite' => $this->formatDates($entity->FechaLimite),
            'seguro_caucion' => $entity->SeguroCaucion,

            'lista_prov' => $entity->ListaProveedores,
            'cert_visita' => $entity->CertificadoVisitaObra,

            'diagrama_gant' => $entity->DiagramaGant,
            'base_condiciones_firmado' => $entity->BaseCondicionesFirmado,
            'condiciones_generales' => $entity->CondicionesGenerales,
            'pliego_tecnico' => $entity->PliegoTecnico,
            'acuerdo_confidencialidad' => $entity->AcuerdoConfidencialidad,
            'legajo_impositivo' => $entity->LegajoImpositivo,
            'antecendentes_referencia' => $entity->AntecedentesReferencias,
            'reporte_accidentes' => $entity->ReporteAccidentes,
            'estructura_costos' => $entity->EstructuraCostos,
            'apu' => $entity->Apu,
            'envio_muestra' => $entity->EnvioMuestras,
            'usuario_califica_reputacion' => isset($entity->UsuarioCalificaReputacion) ? implode(',', $entity->UsuarioCalificaReputacion) : null,
            'moneda' => $entity->Moneda,
            'ficha_tecnica_incluye' => $entity->IncluyePrecalifTecnica,
            'descriptionTitle' => $entity->DescripcionTitle,
            'descriptionDescription' => $entity->DescripcionDescription,
            'descriptionUrl' => $entity->DescripcionURL,
            'tecnico_ofertas' => $entity->TecnicoOfertas,
            'condicion_pago' => $entity->CondicionPago,
            'nom251' => $entity->nom251,
            'distintivo' => $entity->distintivo,
            'filtros_sanitarios' => $entity->filtros_sanitarios,
            'repse' => $entity->repse,
            'poliza' => $entity->poliza,
            'primariesgo' => $entity->primariesgo,
            'obras_referencias' => $entity->obras_referencias,
            'obras_organigrama' => $entity->obras_organigrama,
            'obras_equipos' => $entity->obras_equipos,
            'obras_cronograma' => $entity->obras_cronograma,
            'obras_memoria' => $entity->obras_memoria,
            'obras_antecedentes' => $entity->obras_antecedentes,
            'tarima_ficha_tecnica' => $entity->tarima_ficha_tecnica,
            'tarima_licencia' => $entity->tarima_licencia,
            'tarima_nom_144' => $entity->tarima_nom_144,
            'tarima_acreditacion' => $entity->tarima_acreditacion,
            'concurso_fiscalizado' => $entity->concurso_fiscalizado,
            'edificio_balance' => $entity->edificio_balance,
            'edificio_iva' => $entity->edificio_iva,
            'edificio_cuit' => $entity->edificio_cuit,
            'edificio_brochure' => $entity->edificio_brochure,
            'edificio_organigrama' => $entity->edificio_organigrama,
            'edificio_organigrama_obra' => $entity->edificio_organigrama_obra,
            'edificio_subcontratistas' => $entity->edificio_subcontratistas,
            'edificio_gestion' => $entity->edificio_gestion,
            'edificio_maquinas' => $entity->edificio_maquinas,
            'usuario_fiscalizador' => $entity->UsuarioSupervisor,
        ];

        if ($concurso->is_sobrecerrado) {
            $old_image = $concurso->portrait;
            $old_image_descripcion = $concurso->PortraitDescription;

            $fecha_antigua = $concurso->fecha_limite_economicas;


            $extra_fields = $this->storeBidding($entity, $extra_fields);

            //  $txt = fopen('Li9onelw.txt','w');
            //  fwrite($txt,json_encode($fecha_antigua));
            //  fclose($txt);


            // cambio de fechas
             $fechaInvitacionEdit =
                 $concurso->fecha_limite->format('Y-m-d H:i:s') != $common_fields['fecha_limite'] ? true : false;

            $cambio_fechas = $fecha_antigua->format('Y-m-d H:i:s') != $extra_fields['fecha_limite_economicas'] ? true : false;
            
           

            $fechaFinConsultasEdit =
                $concurso->finalizacion_consultas->format('Y-m-d H:i:s') != $common_fields['finalizacion_consultas'] ? true : false;
/*
                $fechaTecnicaLimitEdit =
                $concurso->ficha_tecnica_fecha_limite != $extra_fields['ficha_tecnica_fecha_limite'] ? true : false;
            $fechaEconomicLimitEdit =
                $concurso->fecha_limite_economicas != $extra_fields['fecha_limite_economicas'] ? true : false;
*/
            $ajustdate =
                ($fechaInvitacionEdit || $fechaFinConsultasEdit || $cambio_fechas /*|| $fechaTecnicaLimitEdit || $fechaEconomicLimitEdit*/) ? true : false;

            $ajustDocumentsTecnical = $this->changeTechnicalDocuments($concurso, $common_fields);

            $estructuraCostosChange =
                $concurso->estructura_costos != $common_fields['estructura_costos'] ? true : false;

            if ($estructuraCostosChange) {
                $ajustDocumentsEconomica += $concurso->estructura_costos === 'si' ?
                    ['estructura_costos' => 'No se requiere Planilla estructura de costos'] : ['estructura_costos' => 'Se requiere Planilla estructura de costos'];
            }

            $apuChange =
                $concurso->apu != $common_fields['apu'] ? true : false;

            if ($apuChange) {
                $ajustDocumentsEconomica += $concurso->apu === 'si' ?
                    ['apu' => 'No se requiere Análisis de Precios Unitarios'] : ['apu' => 'Se requiere Análisis de Precios Unitarios'];
            }


        }

        if ($concurso->is_online) {
            $extra_fields = $this->storeAuction($entity, $extra_fields);
        }

        if ($concurso->is_go) {
            $gos = [
                'type_id' => GoType::all()->first()->id,
                'load_type_id' => $entity->GoLoadType,
                'peso' => $entity->Peso,
                'ancho' => $entity->Ancho,
                'largo' => $entity->Largo,
                'alto' => $entity->Alto,
                'unidades_bultos' => $entity->UnidadesBultos,
                'payment_method_id' => $entity->PaymentMethod,
                'plazo_pago' => $entity->PlazoPago,
                'fecha_desde' =>
                    $entity->FechaDesde ?
                    Carbon::createFromFormat('d-m-Y H:i', $entity->FechaDesde)->format('Y-m-d H:i:s') :
                    null,
                'fecha_hasta' =>
                    $entity->FechaHasta ?
                    Carbon::createFromFormat('d-m-Y H:i', $entity->FechaHasta)->format('Y-m-d H:i:s') :
                    null,
                'calle_desde' => $entity->CalleDesde,
                'calle_hasta' => $entity->CalleHasta,
                'numeracion_desde' => $entity->NumeracionDesde,
                'numeracion_hasta' => $entity->NumeracionHasta,
                'ciudad_desde_id' => $entity->CiudadDesdeSelect,
                'ciudad_hasta_id' => $entity->CiudadHastaSelect,
                'provincia_desde_id' => $entity->ProvinciaDesdeSelect,
                'provincia_hasta_id' => $entity->ProvinciaHastaSelect,
                'nombre_desde' => $entity->NombreDesde,
                'nombre_hasta' => $entity->NombreHasta,
                'cotizar_seguro' => $entity->CotizarSeguro,
                'suma_asegurada' => $entity->SumaAsegurada,
                'cotizar_armada' => $entity->CotizarArmada,
            ];
            $go_documents = $this->mapDocuments($entity);
            $go_additional_documents = $this->mapAdditionalDocuments($entity);
            $extra_fields = $this->storeGO($entity, $extra_fields);
        }

        // Validar
        $fields = array_merge($common_fields, $extra_fields, $gos);

        $validator = $this->validate(
            $entity,
            $fields,
            $concurso->is_sobrecerrado,
            $concurso->is_online,
            $concurso->is_go,
            false
        );

        if ($validator->fails()) {
            return [
                'success' => false,
                'error' => true,
                'message' => $validator->errors()->first(),
                'status' => 422
            ];

        }

        // Guardar concurso
        $concurso->update(array_merge($common_fields, $extra_fields));

        if ($concurso->is_go) {
            // GO
            $go = $concurso->go;
            $go->update($gos);

            $go->documents()->delete();
            $go->additional_documents()->delete();
        }

        $concurso->refresh();

        // GO
        if ($concurso->is_go) {
            // Documentos GCG/NO-GCG
            foreach ($go_documents as $go_document) {
                $go_document->id_go = $go->id;
                $go_document->save();
            }

            // Documentos Adicionales
            foreach ($go_additional_documents as $additional_document) {
                $additional_document->id_go = $go->id;
                $additional_document->save();
            }
        }

        // SOBRECERRADO Y ONLINE
        if ($concurso->is_sobrecerrado || $concurso->is_online) {
            $this->storePortrait($old_image, $entity);
            $this->storePortraitDescription($old_image_descripcion, $entity);
        }



        // Pliegos
        $documentsChanged = $this->storeSheets($concurso, $entity);

        // Productos
        $products_results = $this->storeProducts($concurso, $entity);


        if (!$products_results['success']) {
            return [
                'success' => true,
                'error' => true,
                'message' => $products_results['message'],
                'status' => 422
            ];
        }


        // Plantilla Técnica
            $payroll_results = $this->storePayroll($concurso, $entity, 'edit');

            if (!$payroll_results['success']) {
                return [
                    'success' => true,
                    'error' => true,
                    'message' => $payroll_results['message'],
                    'status' => 422
                ];
            }

        // Oferentes
        if (!$this->storeParticipantes($concurso, $entity)) {
            return [
                'success' => false,
                'error' => true,
                'message' => 'Debe invitar al menos un oferente al concurso.',
                'status' => 422
            ];
        }

        return [
            'success' => true,
            'error' => false,
            'data' => [
                'concurso' => $concurso,
                'ajustdate' => $ajustdate,
                'documentsChanged' => $documentsChanged,
                'products_results' => $products_results,
                'payroll_results' => $payroll_results,
                'tecnicalDocuments' => $ajustDocumentsTecnical,
                'ajustDocumentsEconomica' => $ajustDocumentsEconomica
            ],
            'status' => 200
        ];


    }

    private function formatDates($date)
    {
        return Carbon::createFromFormat('d-m-Y H:i', $date)->format('Y-m-d H:i:s');
    }

    private function changeTechnicalDocuments($concurso, $common_fields)
    {
        $documents = [
            'seguro_caucion' => 'Póliza de seguro de caución',

            'lista_prov' => 'Lista de proveedores',
            'cert_visita' => 'Certificado de visita de obra',

            'diagrama_gant' => 'Diagrama de Gantt/Cronograma de trabajo',
            'condiciones_generales' => 'Condiciones generales FIRMADO',
            'base_condiciones_firmado' => 'Base y condiciones FIRMADO',
            'acuerdo_confidencialidad' => 'Acuerdo de confidencialidad FIRMADO',
            'legajo_impositivo' => 'Legajo Impositivo',
            'antecendentes_referencia' => 'Antecedentes y referencias',
            'reporte_accidentes' => 'Reporte accidentes',
            'envio_muestra' => 'envio de muestra',
            'pliego_tecnico' => 'Pliego técnico FIRMADO',
            'nom251' => 'NOM-251-SSA1-2009',
            'distintivo' => 'Distintivo H',
            'filtros_sanitarios' => 'Filtros Sanitarios Trimestrales a los empleados',
            'repse' => 'Documentación REPSE',
            'poliza' => 'Póliza de seguro responsabilidad civil',
            'primariesgo' => 'Prima de riesgo 5 millones',
            'obras_referencias' => 'Referencias Comerciales',
            'obras_organigrama' => 'Organigrama de obra',
            'obras_equipos' => 'Equipos y herramientas',
            'obras_cronograma' => 'Cronograma de obra',
            'obras_memoria' => 'Memoria técnica',
            'obras_antecedentes' => 'Antecedentes de obras similares',
            'tarima_ficha_tecnica' => 'Ficha Técnica de la tarima',
            'tarima_licencia' => 'Licencia Ambiental integral (LAI)',
            'tarima_nom_144' => 'Cumplimiento NOM-144 SEMARNAT 2017',
            'tarima_acreditacion' => 'Acreditación legal con la procedencia de la madera',
            'edificio_balance' => 'Último balance de la empresa',
            'edificio_iva' => 'Ultimas 3 DDJJ de IVA',
            'edificio_cuit' => 'Constancia de CUIT',
            'edificio_brochure' => 'Brochure de antecedentes de edificios incluyendo obras en curso',
            'edificio_organigrama' => 'Organigrama de la empresa (puestos claves)',
            'edificio_organigrama_obra' => 'Organigrama previsto para la obra',
            'edificio_subcontratistas' => 'Listado de subcontratistas por rubro',
            'edificio_gestion' => 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)',
            'edificio_maquinas' => 'Listado de máquinas y equipos a utilizar',
        ];

        $ajustDocumentsTecnical = [];

        foreach ($documents as $field => $description) {
            if ($concurso->$field != $common_fields[$field]) {
                $ajustDocumentsTecnical[$field] = $concurso->$field === 'si' ?
                    "No se requiere $description" : "Se requiere $description";
            }
        }

        return $ajustDocumentsTecnical;
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
        return sprintf("GMT$sign%02d:%02d", $hours, $mins, $secs);
    }

    public function setToken(Request $request, Response $response, $params){
        $success = false;
        $message = null;
        $status = 200;
        $redirect_url = null;

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $user = user();
            $concurso = $user->customer_company->getAllConcursosByCompany()->find($params['id']);
            $token = substr(str_shuffle('0123456789'), 0, 8);
            $concurso->update([
                'token' => $token
            ]);
            $connection->commit();
            $message = 'Token Generado';
            $emailService = new EmailService();

            $title = 'Token Concurso';
            $subject = "{$concurso->nombre} - $title";

            $template = rootPath(config('app.templates_path')) . '/email/token-send.tpl';

            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'token' => $token,
                'cliente' => $concurso->cliente->full_name
            ]);

            $result = $emailService->send(
                $html,
                $subject,
                //[$concurso->cliente->email],
                [$concurso->supervisor->email],
                $concurso->cliente->full_name
            );
            $success = $result['success'];

            $success = true;
        } catch (Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        try {
            return $this->json($response, [
                'success' => $success,
                'message' => $message,
                'data' => [
                    'redirect' => $redirect_url
                ]
            ], $status);
        } catch (Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}