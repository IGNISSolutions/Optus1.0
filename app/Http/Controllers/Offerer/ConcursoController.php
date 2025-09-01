<?php

namespace App\Http\Controllers\Offerer;

use App\Http\Controllers\BaseController;
use App\Models\Proposal;
use App\Services\EmailService;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\Concurso;
use App\Models\Participante;
use App\Models\Go;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Step;
use App\Services\PaymentServices;
use App\Services\DocumentService;
use DateTimeZone;
use DateTime;
use stdClass;

class ConcursoController extends BaseController
{
    public function serveList(Request $request, Response $response)
    {
        return $this->render($response, 'concurso/list/oferente/type-list.tpl', [
            'page' => 'concursos',
            'accion' => 'listado-oferente',
            'tipo' => 'oferente',
            'title' => 'Monitor - Proveedor'
        ]);
    }

    public function serveDetail(Request $request, Response $response, $params)
    {   
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $secret = getenv('TOKEN_SECRET_KEY');

        $id = (int) $params['id'];

        // VALIDAR TOKEN
        $sessionId = session_id();
        $expectedToken = hash_hmac('sha256', $id . $sessionId, $secret);
        $storedToken   = $_SESSION['edit_token'][$id] ?? null;

        if (!$storedToken || $expectedToken !== $storedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido para acceder al detalle del concurso.'
            ], 403);
        }

        //  Invalida el token después de usarlo
        //unset($_SESSION['edit_token'][$id]);
        
        $concurso = user()->concursos_invitado->find($params['id']);
        abort_if($request, $response, !$concurso, 404);

        return $this->render($response, 'concurso/detail/offerer/detail.tpl', [
            'page' => 'concursos',
            'accion' => 'poretapasoferente',
            'tipo_concurso' => $params['type'],
            'tipo' => $params['step'],
            'idConcurso' => (int) $params['id']
        ]);
    }

    public function guardarTokenAcceso(Request $request, Response $response)
    {   

        $secret = getenv('TOKEN_SECRET_KEY');

        $id = $request->getParsedBody()['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'ID inválido.'
            ], 400);
        }

        $sessionId = session_id();
        $token = hash_hmac('sha256', $id . $sessionId, $secret );

        $_SESSION['edit_token'] = [];

        $_SESSION['edit_token'][$id] = $token;

        return $this->json($response, [
            'success' => true
        ]);
    }

    public function list(Request $request, Response $response)
    {
        $result = $this->listDoFilter();

        $success = $result['success'];
        $message = $result['message'];
        $status = $result['status'];
        $list = $result['list'];

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
                'breadcrumbs' => $breadcrumbs
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
            'ListaConcursosInvitaciones' => [],
            'ListaConcursosTecnicas' => [],
            'ListaConcursosEconomicas' => [],
            'ListaConcursosAnalisis' => [],
            'ListaConcursosAdjudicados' => [],
        ];


        try {
            $user = user();
            $invited = $user->concursos_invitado;
            $invited_with_trashed = $user->concursos_invitado_with_trashed;

            //Checks if Knockout sends filters
            if ($filters) {
                $searchTerm = $filters->searchTerm ?? $filters->query ?? null;
            
                //Checks if an input exist
                if ($searchTerm) {
                    $searchTerm = trim($searchTerm);
            
                    //Checks if the input is numeric (ID filtering)
                    if (is_numeric($searchTerm)) {
                        //Search by exact ID
                        $invited = $invited->filter(function ($item) use ($searchTerm) {
                            return $item->id == $searchTerm;
                        });
            
                        $invited_with_trashed = $invited_with_trashed->filter(function ($item) use ($searchTerm) {
                            return $item->id == $searchTerm;
                        });
                    } else {
                        //Plain text search in name and business_name
                        $invited = $invited->filter(function ($item) use ($searchTerm) {
                            return 
                                    !!stristr($item->nombre, trim($searchTerm)) ||
                                    !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||
                                    !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                    !!stristr($item->area_sol, trim($searchTerm));
                        });
            
                        $invited_with_trashed = $invited_with_trashed->filter(function ($item) use ($searchTerm) {
                            return 
                                    !!stristr($item->nombre, trim($searchTerm)) ||
                                    !!stristr($item->cliente->customer_company->business_name, trim($searchTerm)) ||
                                    !!stristr($item->cliente->full_name, trim($searchTerm)) ||
                                    !!stristr($item->area_sol, trim($searchTerm));
                        });
                    }
                }
            }

            // INVITACIONES
            $concursos = collect();
            $concursos = $invited
                ->filter(function ($concurso) use ($user) {
                    date_default_timezone_set($concurso->cliente->customer_company->timeZone);
                    return $concurso->oferentes
                        ->where('id_offerer', $user->offerer_company_id)
                        ->where('rechazado', false)
                        ->where('is_invitacion_pendiente', true)
                        ->where('has_invitacion_vencida', false)
                        ->count() > 0;
                })
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                array_push($list['ListaConcursosInvitaciones'], $this->mapConcursoList($concurso));
            }

            // TÉCNICAS
            $concursos = collect();
            $concursos = $invited
                ->where('technical_includes', true)
                ->filter(function ($concurso) use ($user) {
                    date_default_timezone_set($concurso->cliente->customer_company->timeZone);
                    return $concurso->oferentes
                        ->where('id_offerer', $user->offerer_company_id)
                        ->whereIn(
                            'etapa_actual',
                            array_merge(
                                Participante::ETAPA_TECNICA_PENDIENTE,
                                Participante::ETAPA_TECNICA_PRESENTADA,
                                Participante::ETAPA_TECNICA_DECLINADA,
                                Participante::ETAPA_TECNICA_RECHAZADA
                            )
                        )
                        ->count() > 0;
                })
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                array_push($list['ListaConcursosTecnicas'], $this->mapConcursoList($concurso));
            }

            // ECONÓMICAS
            $concursos = collect();
            $concursos = $invited
                ->filter(
                    function ($concurso) use ($user) {
                        date_default_timezone_set($concurso->cliente->customer_company->timeZone);
                        return (
                            ($concurso->is_sobrecerrado || $concurso->is_go) &&
                            $concurso->oferentes
                                ->where('id_offerer', $user->offerer_company_id)
                                ->whereIn(
                                    'etapa_actual',
                                    Participante::ETAPAS_ECONOMICAS
                                )
                                ->count() > 0
                        ) ||
                            (
                                ($concurso->is_online) &&
                                $concurso->oferentes
                                    ->where('id_offerer', $user->offerer_company_id)
                                    ->whereIn('etapa_actual', [
                                        Participante::ETAPAS['economica-pendiente'],
                                        Participante::ETAPAS['economica-2da-pendiente'],
                                        Participante::ETAPAS['economica-presentada']
                                    ])
                                    ->count() > 0
                            );
                    }
                )->filter(function ($concurso) use ($user) {

                    return ($concurso->oferentes
                        ->where('id_offerer', $user->offerer_company_id)
                        ->where(
                            'etapa_actual',
                            '!=',
                            'economica-declinada'
                        )
                        ->count() > 0);
                })
                ->filter(function ($concurso) {
                    date_default_timezone_set($concurso->cliente->customer_company->timeZone);
                    if ($concurso->is_online) {
                        return
                            ($concurso->countdown || $concurso->timeleft) &&
                            $concurso->fin_subasta > Carbon::now();
                    } else {
                        return $concurso->fecha_limite_economicas > Carbon::now() || $concurso->segunda_ronda_fecha_limite > Carbon::now() || $concurso->fecha_limite_economicas > Carbon::now()->subDays(30) ;
                    }
                })
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                array_push($list['ListaConcursosEconomicas'], $this->mapConcursoList($concurso));
            }

            // ANÁLISIS
            $concursos = collect();
            $concursos = $invited
                ->filter(
                    function ($concurso) use ($user) {
                        return $concurso->oferentes
                            ->where('id_offerer', $user->offerer_company_id)
                            ->where('rechazado', false)
                            ->whereIn('etapa_actual', [
                                Participante::ETAPAS['economica-revisada']
                            ])
                            ->count() > 0;
                    }
                )
                ->filter(
                    function ($concurso) {
                        if ($concurso->is_online) {
                            return !$concurso->countdown;
                        }
                        return true;
                    }
                )
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                array_push($list['ListaConcursosAnalisis'], $this->mapConcursoList($concurso));
            }

            // ADJUDICADOS
            $concursos = collect();
            $concursos = $invited
                ->filter(
                    function ($concurso) use ($user) {
                        return $concurso->oferentes
                            ->where('id_offerer', $user->offerer_company_id)
                            ->whereIn('etapa_actual', [
                                Participante::ETAPAS['adjudicacion-pendiente'],
                                Participante::ETAPAS['adjudicacion-aceptada'],
                                Participante::ETAPAS['adjudicacion-rechazada']
                            ])
                            ->count() > 0;
                    }
                )
                ->sortBy('id');

            foreach ($concursos as $concurso) {
                $concurso_oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

                array_push(
                    $list['ListaConcursosAdjudicados'],
                    array_merge(
                        $this->mapConcursoList($concurso),
                        [
                            'EstadoAdjudicacion' => $concurso_oferente->adjudicacion_status
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

        return [
            'success' => $success,
            'message' => $message,
            'list' => $list,
            'status' => $status
        ];
    }

    /**
     * @param $concuro
     * @param $type
     * Enable invitations for competitions
     */
    private static function hasCompetitionsEnableInvitations($concurso, $type)
    {
        /**
         * $has_date_enabled        = verifica fecha limite económica
         * $has_duration            = verifica durecion, solo para concursos online
         * $has_competition_enabled = habilita invitacion por tipo de concurso
         */
        $has_date_enabled = $concurso->fecha_limite_economicas ? true : false;

        $has_duration = $concurso->inicio_subasta & $concurso->duracion ? true : false;

        $has_competition_enabled = $concurso->tipo_concurso == $type & $has_date_enabled ? true : false;

        $has_competition_online_enabled = $concurso->is_online & $has_duration ? true : false;

        $result['message'] = [
            'has_type' => $type,
            'enabled' => $has_competition_enabled || $has_competition_online_enabled ? true : false
        ];

        return $result;
    }

    private function mapConcursoList($concurso)
    {
        return [
            'Id' => $concurso->id,
            'Nombre' => $concurso->nombre,
            'Solicitante' => $concurso->cliente->customer_company->business_name,
            'FechaLimite' => $concurso->fecha_limite->format('d-m-Y'),
            'FechaLimiteOrden' => $concurso->fecha_limite->format('Y-m-d H:i:s'),
            'TipoConcurso' => $concurso->tipo_concurso_nombre,
            'TipoOperacion' => $concurso->alcance->nombre,
            'TipoConcursoPath' => $concurso->tipo_concurso,
            'AreaSolicitante' => $concurso->area_sol,
            'UsuarioSolicitante' => $concurso->cliente->full_name
        ];
    }

    public function detail(Request $request, Response $response, $params)
    {

        $success = false;
        $message = null;
        $status = 200;
        $list = [
            'IsGo' => false,
            'IsSobrecerrado' => false,
            'IsOnline' => false
        ];

        try {
            $user = user();

            // Obtener concurso
            $concurso = $user->concursos_invitado->find($params['id']);
            $rondaActual = $concurso->ronda_actual;
            $title = $rondaActual > 1 ? Concurso::NUEVAS_RONDAS[$rondaActual] : '';
            date_default_timezone_set($concurso->cliente->customer_company->timeZone);


            $list['IsGo'] = $concurso->is_go;
            $list['IsOnline'] = $concurso->is_online;
            $list['IsSobrecerrado'] = $concurso->is_sobrecerrado;

            // Obtener el oferente
            $oferente = $concurso->oferentes->where('id_offerer', $user->offerer_company_id)->first();

            $terminos = ''; // evitar undefined
            $templatesPath = rootPath(config('app.templates_path'));

            $tplDefault = $templatesPath . '/terminos-oferente.tpl';
            $tplIsyn    = $templatesPath . '/terminos-oferente-isyn.tpl';

            // 1) Obtener el id del usuario que creó el concurso (id_cliente)
            $creatorUserId = null;
            if (isset($concurso->id_cliente)) {
                $creatorUserId = (int) $concurso->id_cliente;
            } else {
                // fallback por las dudas de no tener la propiedad cargada
                $creatorUserId = (int) Concurso::where('id', $concurso->id)->value('id_cliente');
            }

            // 2) Con ese usuario, obtener su customer_company_id
            $creatorCustomerCompanyId = null;
            if ($creatorUserId) {
                $creatorCustomerCompanyId = (int) \App\Models\User::where('id', $creatorUserId)->value('customer_company_id');
            }

            // 3) ¿El creador pertenece a la company 17?
            $isIsynConcurso = ($creatorCustomerCompanyId === 17);

            // 4) Elegir el tpl
            $terminos_filename = ($isIsynConcurso && is_file($tplIsyn)) ? $tplIsyn : $tplDefault;

            // 5) Render del tpl elegido
            if (is_file($terminos_filename)) {
                ob_start();
                include $terminos_filename;
                $terminos = ob_get_clean();
            }
            $descriptionImg = $concurso->descriptionImagen == null ? "/default.gif" : $concurso->descriptionImagen;
            $descriptionText = $concurso->descriptionDescription == null ? null : $concurso->descriptionDescription;
            if ($concurso->descriptionImagen == null || $concurso->descriptionImagen == "default.gif") {
                $textAndImg = "<div><p>" . $concurso->descriptionDescription . "</p></div>";
            } else {
                $textAndImg = "<div> <img src=" . filePath(config('app.images_path')) . $descriptionImg . " width='300' height='300' style='float: right'><p>" . $concurso->descriptionDescription . "</p></div>";
            }
            $description = $textAndImg;

            $route_name = 'concursos.oferente.serveDetail';

            // COMMON
            $common = [
                'IdConcurso' => $concurso->id,
                'UserId' => $oferente->id_offerer,
                'OferenteId' => $oferente->id,
                'Nombre' => $concurso->nombre,
                'Solicitante' => $concurso->cliente->customer_company->business_name,
                'Administrador' => ucwords(strtolower($concurso->cliente->full_name)),
                'TipoConcurso' => $concurso->tipo_concurso_nombre,
                'TipoOperacion' => $concurso->alcance->nombre,
                'Portrait' => $concurso->portrait,
                'ImagePath' => filePath(config('app.images_path')),
                'FilePath' => filePath($concurso->file_path, true),
                'FilePathOferente' => filePath($oferente->file_path),
                'TerminosCondiciones' => $terminos,
                'Resena' => $concurso->resena,
                'Descripcion' => $description,
                'Pais' => $concurso->pais,
                'Provincia' => $concurso->provincia,
                'Localidad' => $concurso->localidad,
                'Direccion' => $concurso->direccion,
                'Cp' => $concurso->cp,
                'Latitud' => $concurso->latitud,
                'Longitud' => $concurso->longitud,
                'TipoConcursoPath' => $concurso->tipo_concurso,
                'AdjudicacionAnticipada' => $concurso->adjudicacion_anticipada,
                'FechaDesde' => $concurso->is_go ? $concurso->go->fecha_desde->format('d-m-Y') : null,
                'HoraDesde' => $concurso->is_go ? $concurso->go->fecha_desde->format('H:i:s') : null,
                'FechaHasta' => $concurso->is_go ? $concurso->go->fecha_hasta->format('d-m-Y') : null,
                'HoraHasta' => $concurso->is_go ? $concurso->go->fecha_hasta->format('H:i:s') : null,
                'ProvinciaDesdeNombre' => isset($concurso->go->province_from->nombre) ? $concurso->go->province_from->nombre : '',
                'ProvinciaHastaNombre' => isset($concurso->go->province_to->nombre) ? $concurso->go->province_to->nombre : '',
                'CiudadDesdeNombre' => isset($concurso->go->city_from->nombre) ? $concurso->go->city_from->nombre : '',
                'CiudadHastaNombre' => isset($concurso->go->city_to->nombre) ? $concurso->go->city_to->nombre : '',
                'AceptoInvitacion' => $oferente->invitation->status->description,
                'IsInvitacionPendiente' => $oferente->is_invitacion_pendiente,
                'AceptacionInvitacion' => $concurso->fecha_limite->format('d-m-Y'),
                'AceptacionInvitacionHora' => $concurso->fecha_limite->format('H:i:s'),
                'CierreMuroConsultas' => $concurso->finalizacion_consultas->format('d-m-Y'),
                'CierreMuroConsultasHora' => $concurso->finalizacion_consultas->format('H:i:s'),
                'IncluyeTecnica' => $concurso->technical_includes ? true : false,
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
                'EstadoSubasta' => $concurso->subasta_status,
                'EstadoTecnica' => $oferente->has_tecnica_presentada ? $oferente->technical_proposal->status->description : ($oferente->has_tecnica_vencida ? 'Plazo Vencido' : 'Pendiente'),
                'EstadoEconomica' => $oferente->has_economica_presentada ? $oferente->economic_proposal->status->description : ($oferente->has_economica_vencida ? 'Plazo Vencido' : 'Pendiente'),
                'EstadoChat' => $oferente->is_chat_enabled ? 'Habilitado' : 'Deshabilitado',
                'Moneda' => $concurso->tipo_moneda->nombre,
                'Estado' => $concurso->is_finalizado ? 'finalizado' : ($concurso->adjudicado ? 'adjudicado' : 'pendiente'),
                'IsInvitacionRechazada' => $oferente->is_invitacion_rechazada,
                'IsTecnicaPendiente' => $oferente->is_tecnica_pendiente,
                'IsTecnicaPresentada' => $oferente->is_tecnica_presentada,
                'HasTecnicaVencida' => $oferente->has_tecnica_vencida,
                'IsEconomicaPendiente' => $oferente->is_economica_pendiente,
                'IsEconomicaPendienteSegundaRonda' => $oferente->is_economica_pendiente,
                'IsEconomicaPresentada' => $oferente->is_economica_presentada,
                'HasEconomicaRevisada' => $oferente->has_economica_revisada,
                'HasEconomicaVencida' => $oferente->has_economica_vencida,
                'IsAdjudicacionPendiente' => $oferente->is_adjudicacion_pendiente,
                'IsAdjudicacionAceptada' => $oferente->is_adjudicacion_aceptada,
                'IsAdjudicacionRechazada' => $oferente->is_adjudicacion_rechazada,
                'Rechazado' => $oferente->rechazado,
                'HasEconomicaPresentada' => $oferente->has_economica_presentada,
                'HasTecnicaPresentada' => $oferente->has_tecnica_presentada,
                'HasTecnicaAprobada' => $oferente->has_tecnica_aprobada,
                'ShowTechnical' => $oferente->show_technical,
                'ShowEconomic' => $oferente->show_economic,
                'EnableTechnical' => $oferente->enable_technical,
                'EnableEconomic' => $oferente->enable_economic,
                'Adjudicado' => $concurso->adjudicado,
                'Eliminado' => $concurso->trashed(),
                'ZonaHoraria' => $this->toGmtOffset($concurso->cliente->customer_company->timeZone),
                'DescripcionTitle' => $concurso->descriptionTitle,
                'DescripcionDescription' => $concurso->descriptionDescription,
                'DescripcionUrl' => $concurso->descriptionUrl,
                'DescripcionImagen' => $concurso->descriptionImagen,
                'CondicionPago' => $concurso->condicion_pago,
                'urlChatMuro' => route($route_name, ['type' => $concurso->tipo_concurso, 'id' => $concurso->id, 'step' => 'chat-muro-consultas']),
                //'ChatEnable' => $oferente->is_chat_enabled,
                'ChatEnable' => $concurso->is_sobrecerrado ? true : ($concurso->chat == 'si' ? true : false ),
                'ShowChatButton' => $oferente->has_invitacion_aceptada
            ];

            // INVITACIÓN
            if ($params['step'] === Step::STEPS['offerer']['invitacion']) {
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
                    array_push($products, [
                        'product_id' => $product->id,
                        'product_name' => $product->nombre,
                        'product_description' => $product->descripcion,
                        'currency_id' => $concurso->tipo_moneda->id,
                        'currency_name' => $concurso->tipo_moneda->nombre,
                        'minimum_quantity' => $product->oferta_minima,
                        'total_quantity' => $product->cantidad,
                        'measurement_id' => $product->unidad_medida->id,
                        'measurement_name' => $product->unidad_medida->name
                    ]);
                }


                $list = array_merge($list, array_merge($common, [
                    'Productos' => $products,
                    'Media' => $media,
                    'PlazoVencidoAceptacion' => $concurso->fecha_limite < Carbon::now(),
                    'AceptacionTerminos' => $concurso->aceptacion_terminos
                ]));
            }

            // CHAT
            if ($params['step'] === Step::STEPS['offerer']['chat-muro-consultas']) {
                $list = array_merge($list, $common);
            }

            // TÉCNICA
            if ($params['step'] === Step::STEPS['offerer']['tecnica']) {
                // GO
                if ($concurso->is_go) {
                    // $documentService = new DocumentService();
                    // $gcg_lists = json_decode($documentService->getLists($oferente->company->cuit));
                    $go_driver_documents = [];
                    $go_vehicle_documents = [];
                    $go_trailer_documents = [];
                    $go_nogcg_driver_documents = [];
                    $additional_driver_documents = [];
                    $additional_vehicle_documents = [];

                    // DOCUMENTOS GCG y NO-GCG
                    foreach ($concurso->go->documents as $go_document) {
                        $existent_document = $oferente->go_documents
                            ->where('id_go_document', $go_document->id)
                            ->first();
                        $participante_document = [
                            'id' => $existent_document ? $existent_document->id : null,
                            'document_id' => $go_document->id,
                            'name' => $go_document->document->name,
                            'cuit' => $go_document->cuit,
                            'razon_social' => $go_document->razon_social,
                            'filename' => $existent_document ? $existent_document->filename : null,
                            'success' => false,
                            'action' => null,
                            'types' => $go_document->document->types->pluck('description', 'code')
                        ];
                        if ($go_document->document->is_driver) {
                            if ($go_document->document->is_gcg) {
                                $go_driver_documents[] = $participante_document;
                            } else {
                                $go_nogcg_driver_documents[] = $participante_document;
                            }
                        } else {
                            if ($go_document->document->is_vehicle) {
                                $go_vehicle_documents[] = $participante_document;
                            }
                            if ($go_document->document->is_trailer) {
                                $go_trailer_documents[] = $participante_document;
                            }
                        }
                    }

                    // DOCUMENTOS ADICIONALES
                    foreach ($concurso->go->additional_documents as $additional_document) {
                        $existent_document = $oferente->go_documents
                            ->where('id_go_document_additional', $additional_document->id)
                            ->first();

                        $participante_document = [
                            'id' => $existent_document ? $existent_document->id : null,
                            'document_id' => $additional_document->id,
                            'name' => $additional_document->name,
                            'filename' => $existent_document ? $existent_document->filename : null
                        ];

                        if ($additional_document->type == DocumentType::TYPE_SLUGS['driver']) {
                            $additional_driver_documents[] = $participante_document;
                        } else {
                            $additional_vehicle_documents[] = $participante_document;
                        }
                    }

                    $list = array_merge($list, array_merge($common, [
                        'Tipologia' => $concurso->go->type->name,
                        // 'ArchivoGoNoArt' => $participante_document->ArchivoGoNoArt,
                        // 'ArchivoGoBene' => $participante_document->ArchivoGoBene,
                        // 'Drivers' =>  $gcg_lists->data->drivers,
                        'DriverSelected' => $oferente->id_conductor,
                        // 'Vehicles' => $gcg_lists->data->vehicles,
                        'VehicleSelected' => $oferente->id_vehiculo,
                        // 'Trailers' => $gcg_lists->data->trailers,
                        'TrailerSelected' => $oferente->id_trailer,
                        'DriverDocuments' => $go_driver_documents,
                        'VehicleDocuments' => $go_vehicle_documents,
                        'TrailerDocuments' => $go_trailer_documents,
                        'DriverNoGcgDocuments' => $go_nogcg_driver_documents,
                        // 'Amount' => $document_amount,
                        'AdditionalDriverDocuments' => $additional_driver_documents,
                        'AdditionalVehicleDocuments' => $additional_vehicle_documents
                    ]));
                    // NO-GO
                } else {
                    $list = array_merge($list, array_merge($common, [
                        'PropuestasTecnicas' => $concurso->technical_includes ? $this->parsed_technical_proposal($concurso, $oferente) : [],
                        'TechnicalEvaluations' => $concurso->plantilla_tecnica == null ? null : $concurso->plantilla_tecnica->parsed_items,
                        'TechnicalProposal' => $oferente->parsed_technical_proposal,
                        'SeguroCaucion' => isset($concurso->seguro_caucion) ? $concurso->seguro_caucion : 'no',
                        'DiagramaGant' => isset($concurso->diagrama_gant) ? $concurso->diagrama_gant : 'no',

                        'ListaProveedores' => isset($concurso->lista_prov) ? $concurso->lista_prov : 'no',
                        'CertificadoVisitaObra' => isset($concurso->cert_visita) ? $concurso->cert_visita : 'no',

                        'EntregaDocEvaluacion' => isset($concurso->entrega_doc_evaluacion) ? $concurso->entrega_doc_evaluacion : 'no',
                        'RequisitosLegales' => isset($concurso->requisitos_legales) ? $concurso->requisitos_legales : 'no',
                        'ExperienciaYReferencias' => isset($concurso->experiencia_y_referencias) ? $concurso->experiencia_y_referencias : 'no',
                        'DocumentacionREPSE' => isset($concurso->repse_two) ? $concurso->repse_two : 'no',
                        'Alcance' => isset($concurso->alcance_two) ? $concurso->alcance_two : 'no',
                        'FormaPago' => isset($concurso->forma_pago) ? $concurso->forma_pago : 'no',
                        'TiempoFabricacion' => isset($concurso->tiempo_fabricacion) ? $concurso->tiempo_fabricacion : 'no',
                        'FichaTecnica' => isset($concurso->ficha_tecnica) ? $concurso->ficha_tecnica : 'no',
                        'Garantias' => isset($concurso->garantias) ? $concurso->garantias : 'no',


                        'BaseCondiciones' => isset($concurso->base_condiciones_firmado) ? $concurso->base_condiciones_firmado : 'no',
                        'CondicionesGenerales' => isset($concurso->condiciones_generales) ? $concurso->condiciones_generales : 'no',
                        'PliegoTecnico' => isset($concurso->pliego_tecnico) ? $concurso->pliego_tecnico : 'no',
                        'Confidencialidad' => isset($concurso->acuerdo_confidencialidad) ? $concurso->acuerdo_confidencialidad : 'no',
                        'LegajoImpositivo' => isset($concurso->legajo_impositivo) ? $concurso->legajo_impositivo : 'no',
                        'Antecedentes' => isset($concurso->antecendentes_referencia) ? $concurso->antecendentes_referencia : 'no',
                        'ReporteAccidentes' => isset($concurso->reporte_accidentes) ? $concurso->reporte_accidentes : 'no',
                        'EnvioMuestra' => isset($concurso->envio_muestra) ? $concurso->envio_muestra : 'no',
                        'nom251' => isset($concurso->nom251) ? $concurso->nom251 : 'no',
                        'distintivo' => isset($concurso->distintivo) ? $concurso->distintivo : 'no',
                        'filtros_sanitarios' => isset($concurso->filtros_sanitarios) ? $concurso->filtros_sanitarios : 'no',
                        'repse' => isset($concurso->repse) ? $concurso->repse : 'no',
                        'poliza' => isset($concurso->poliza) ? $concurso->poliza : 'no',
                        'primariesgo' => isset($concurso->primariesgo) ? $concurso->primariesgo : 'no',
                        'obras_referencias' => isset($concurso->obras_referencias) ? $concurso->obras_referencias : 'no',
                        'obras_organigrama' => isset($concurso->obras_organigrama) ? $concurso->obras_organigrama : 'no',
                        'obras_equipos' => isset($concurso->obras_equipos) ? $concurso->obras_equipos : 'no',
                        'obras_cronograma' => isset($concurso->obras_cronograma) ? $concurso->obras_cronograma : 'no',
                        'obras_memoria' => isset($concurso->obras_memoria) ? $concurso->obras_memoria : 'no',
                        'obras_antecedentes' => isset($concurso->obras_antecedentes) ? $concurso->obras_antecedentes : 'no',
                        'tarima_ficha_tecnica' => isset($concurso->tarima_ficha_tecnica) ? $concurso->tarima_ficha_tecnica : 'no',
                        'tarima_licencia' => isset($concurso->tarima_licencia) ? $concurso->tarima_licencia : 'no',
                        'tarima_nom_144' => isset($concurso->tarima_nom_144) ? $concurso->tarima_nom_144 : 'no',
                        'tarima_acreditacion' => isset($concurso->tarima_acreditacion) ? $concurso->tarima_acreditacion : 'no',
                        'edificio_balance' => isset($concurso->edificio_balance) ? $concurso->edificio_balance : 'no',
                        'edificio_iva' => isset($concurso->edificio_iva) ? $concurso->edificio_iva : 'no',
                        'edificio_cuit' => isset($concurso->edificio_cuit) ? $concurso->edificio_cuit : 'no',
                        'edificio_brochure' => isset($concurso->edificio_brochure) ? $concurso->edificio_brochure : 'no',
                        'edificio_organigrama' => isset($concurso->edificio_organigrama) ? $concurso->edificio_organigrama : 'no',
                        'edificio_organigrama_obra' => isset($concurso->edificio_organigrama_obra) ? $concurso->edificio_organigrama_obra : 'no',
                        'edificio_subcontratistas' => isset($concurso->edificio_subcontratistas) ? $concurso->edificio_subcontratistas : 'no',
                        'edificio_gestion' => isset($concurso->edificio_gestion) ? $concurso->edificio_gestion : 'no',
                        'edificio_maquinas' => isset($concurso->edificio_maquinas) ? $concurso->edificio_maquinas : 'no'
                    ]));
                }
            }

            // ECONÓMICA
            if ($params['step'] === Step::STEPS['offerer']['economica']) {
                $economic_proposal = $oferente->parsed_economic_proposal;

                $array = json_decode(json_encode($economic_proposal->values), true);

                // Mapear los valores anteriores desde Items a EconomicProposal
                $items_map = [];
                foreach (json_decode(json_encode($concurso->getCotizacionesOutputByUser()), true) as $item) {
                    if (isset($item['valores']) && isset($item['id'])) {
                        $items_map[$item['id']] = $item['valores'];
                    }
                }

                // Recorremos EconomicProposal y metemos los valores del item si existen
                foreach ($array as $i => $producto) {
                    $productId = $producto['product_id'];
                    if (isset($items_map[$productId])) {
                        $valores = $items_map[$productId];
                        $array[$i]['cotizacion'] = $valores['cotizacion'] ?? 0;
                        $array[$i]['cantidad'] = $valores['cantidad'] ?? 0;
                        $array[$i]['fecha'] = $valores['fecha'] ?? 0;
                        $array[$i]['creado'] = $valores['creado'] ?? null;
                    }
                    $array[$i]['maximum_cotizacion'] = null;
                }

                $economic_proposal->values = json_decode(json_encode($array));

                $list = array_merge($list, array_merge($common, [
                    'Costs' => $concurso->estructura_costos ?? 'no',
                    'AnalisisApu' => $concurso->apu ?? 'no',
                    'EconomicProposal' => $economic_proposal,
                    'Descendente' => $concurso->tipo_valor_ofertar == 'descendente',
                    'PermiteAnularOferta' => $concurso->permitir_anular_oferta === 'si',
                    'CantidadOferentes' => $concurso->oferentes
                        ->whereIn('etapa_actual', [
                            Participante::ETAPAS['economica-pendiente'],
                            Participante::ETAPAS['economica-2da-pendiente'],
                            Participante::ETAPAS['economica-presentada'],
                            Participante::ETAPAS['adjudicacion-pendiente']
                        ])->count(),
                    'Duracion' => isset($concurso->parsed_duracion)
                        ? $concurso->parsed_duracion[0] . ' minutos ' . $concurso->parsed_duracion[1] . ' segundos'
                        : ' 0 minutos 0 segundos',
                    'TiempoAdicional' => $concurso->tiempo_adicional,
                    'Countdown' => $concurso->countdown,
                    'Timeleft' => $concurso->timeleft,
                    'UnidadMinima' => $concurso->unidad_minima,
                    'VerNumOferentesParticipan' => $concurso->ver_num_oferentes_participan,
                    'VerOfertaGanadora' => $concurso->ver_oferta_ganadora,
                    'VerRanking' => $concurso->ver_ranking,
                    'VerTiempoRestante' => $concurso->ver_tiempo_restante,
                    'Chat' => $concurso->chat,
                    'SoloOfertasMejores' => $concurso->solo_ofertas_mejores,
                    'PrecioMaximo' => $concurso->precio_maximo,
                    'PrecioMinimo' => $concurso->precio_minimo,
                    'RondaActual' => $rondaActual,
                    'Title' => $title
                ]));

                if ($concurso->is_online) {
                    $list = array_merge($list, $concurso->getSubastaOutputByUser());
                } else {
                    $list = array_merge($list, [
                        'Items' => $concurso->getCotizacionesOutputByUser()
                    ]);
                }
            }

            // ANÁLISIS
            if ($params['step'] === Step::STEPS['offerer']['analisis']) {
                $economic_proposal = $oferente->parsed_economic_proposal;
                if ($oferente->is_economica_pendiente_segunda_ronda) {
                    $economic_proposal->comment = '';
                    $objDocument = (object) array(
                        "id" => null,
                        "type_id" => 2,
                        "name" => "Propuesta Económica",
                        "filename" => null,
                        "action" => null
                    );
                    $economic_proposal->documents = [$objDocument];

                    $array = json_decode(json_encode($economic_proposal->values), true);
                    foreach ($array as $key => $value)
                        $array[$key]['maximum_cotizacion'] = $array[$key]['cotizacion'];
                    $arrStdClass = json_decode(json_encode($array));
                    $economic_proposal->values = $arrStdClass;
                } else {
                    $array = json_decode(json_encode($economic_proposal->values), true);
                    foreach ($array as $key => $value)
                        $array[$key]['maximum_cotizacion'] = 9999999999;
                    $arrStdClass = json_decode(json_encode($array));
                    $economic_proposal->values = $arrStdClass;
                }
                $list = array_merge($list, array_merge($common, [
                    'EconomicProposal' => $economic_proposal,
                    'Items' => $concurso->adjudicacion_items,
                
                ]));
            }

            // ADJUDICACIÓN
            if ($params['step'] === Step::STEPS['offerer']['adjudicado']) {

                $list = array_merge($list, array_merge($common, [
                    'EstadoTran' => isset($oferente->payment->paid) ? $oferente->payment->paid : '',
                    'UrlMercadoPago' => PaymentServices::generatePreference($concurso->id, $oferente->id),
                    'Cuit' => $concurso->cliente->customer_company->cuit,
                    'PersonaContacto' => ucwords(strtolower($concurso->cliente->customer_company->business_name)),
                    'Apellido' => ucwords(strtolower($concurso->cliente->customer_company->apellido)),
                    'Telefono' => $concurso->cliente->customer_company->telefono,
                    'Email' => $concurso->cliente->customer_company->email,
                    'Items' => $concurso->adjudicacion_items,
                    'Resultados' => $concurso->adjudicacion_resultados_output,
                    'AceptoAdjudicacion' => (string) $oferente->acepta_adjudicacion,
                    
                ]));
            }
            



            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Concursos', 'url' => null],
                ['description' => 'Monitor', 'url' => '/concursos/oferente'],
                ['description' => getStepName($params['step'], $concurso->is_go), 'url' => null]
            ];


            $success = true;

                    // Aquí agregamos las líneas para escribir $list en el archivo
        $file2 = fopen("Lista3.txt", "w");
        fwrite($file2, json_encode($list, JSON_PRETTY_PRINT)); // Usé json_encode para que sea legible
        fclose($file2); // Cerramos el archivo después de escribir

            
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
                'breadcrumbs' => $breadcrumbs,
                'steps' => Step::getByConcurso($concurso, $params['step'])
            ]
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

    public function declination(Request $request, Response $response)
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

            $concurso = Concurso::find(intval($body->IdConcurso));
            $oferente = $concurso->oferentes->where('id_offerer', user()->offerer_company_id)->first();
            $template = rootPath(config('app.templates_path')) . '/email/offerer-declination.tpl';
            $title = 'Proveedor declina participación';

            $etapa_actual = null;
            $etapa_declination = null;
            $rondaTec = $oferente->ronda_tecnica;
            if ($oferente->is_tecnica_pendiente || $oferente->is_tecnica_presentada) {
                $etapa_actual = $rondaTec == 1 ? Participante::ETAPA_TECNICA_DECLINADA['tecnica-declinada'] : Participante::ETAPA_TECNICA_DECLINADA['tecnica-declinada-' . $rondaTec];
                $etapa_declination = 'Etapa Técnica';
            }

            if ($oferente->is_economica_pendiente || $oferente->is_economica_pendiente_segunda_ronda || $oferente->is_economica_presentada || $oferente->is_economica_revisada) {
                $etapa_actual = Participante::ETAPAS['economica-declinada'];
                $etapa_declination = 'Etapa Económica';
            }
            $fechaDecl = Carbon::now();
            $oferente->update([
                'reasonDeclination' => $body->reason,
                'fecha_declination' => $fechaDecl->format('Y-m-d'),
                'etapa_actual' => $etapa_actual
            ]);

            $message = 'Ha declinado su participación';


            $subject = $concurso->nombre . ' - ' . $title;
            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $concurso,
                'oferente' => $oferente,
                'reason' => $oferente->reasonDeclination,
                'fecha_declination' => $fechaDecl,
                'etapa' => $etapa_declination
            ]);

            $result = $emailService->send(
                $html,
                $subject,
                [$concurso->cliente->email],
                $concurso->cliente->full_name
            );
            $success = $result['success'];
            if ($success) {
                $connection->commit();
                $message = 'Concurso declinado con éxito.';
                $redirect_url = route('concursos.oferente.serveList');
            } else {
                $connection->rollBack();
                $message = $message ?? 'Han ocurrido errores al enviar las notificaciones.';
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

    public function parsed_technical_proposal($concurso, $oferente)
    {
        return [
            'TechnicalEvaluations' => $concurso->plantilla_tecnica->parsed_items,
            'rondas' => $this->parseRounds($oferente)
        ];
    }

    protected function parseRounds($oferente)
    {
        $rondasTech = [];
        $rondaActual = $oferente->ronda_tecnica;
        for ($ronda = 1; $ronda <= $rondaActual; $ronda++) {
            $tecnica = $oferente->technicalByRound($ronda);
            $comentarioNuevaRonda = $tecnica ? $tecnica->comentario_nueva_ronda : null;
            $etapaPendding = $oferente->isTecnicaByRoundByStep($ronda, 'pendiente');
            $etapaPresented = !$etapaPendding && $tecnica ? true : false;
            $etapaDeclinated = $oferente->isTecnicaByRoundByStep($ronda, 'declinada');
            $etapaRejected = $oferente->isTecnicaByRoundByStep($ronda, 'rechazada');
            $title = Participante::RONDAS[$ronda];
            $documents = $oferente->parsedTechnicalByRound($ronda);
            $roundActive = $rondaActual == $ronda ? true : false;
            $eval = $roundActive ? ($oferente->analisis_tecnica_valores ? $oferente->analisis_tecnica_valores[0] : null) : null;
            $rondasTech[] = [
                'title' => $title . ' técnica',
                'active' => $roundActive,
                'refRound' => str_replace(' ', '', $title),
                'comentario' => $tecnica ? $tecnica->comment : null,
                'comentarioNuevaRonda' => $comentarioNuevaRonda,
                'documents' => $documents,
                'pending' => $etapaPendding,
                'presented' => $etapaPresented,
                'declinated' => $etapaDeclinated,
                'rejected' => $etapaRejected,
                'evaluacion' => $eval,
                'comentarioDeclinacion' => $etapaDeclinated ? $oferente->reasonDeclination : null,
                'fechaDeclinacion' => $etapaDeclinated ? $oferente->fecha_declination->format('d-m-Y') : null
            ];
        }
        return $rondasTech;
    }
}