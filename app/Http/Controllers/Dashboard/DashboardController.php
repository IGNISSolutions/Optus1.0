<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Concurso;
use App\Models\Participante;
use App\Models\User;
use App\Models\AdjudicationApproval;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    public function serveList(Request $request, Response $response)
    {
        $type = $_SESSION["type"];
        $viewOfferer = 'dashboard/offerer/list.tpl';
        $viewCustomer = 'dashboard/customer/list.tpl';

        if ($type == "offerer") {
            return $this->render($response, $viewOfferer, [
                'page'  => 'dashboard',
                'tipo'  => 'detalle',
                'title' => 'Dashboard'
            ]);
        } else {
            return $this->render($response, $viewCustomer, [
                'page'  => 'dashboard',
                'tipo'  => 'detalle',
                'title' => 'Dashboard'
            ]);
        }
    }

    public function list(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [
            'Invitaciones' => [],
            'Consultas'    => [],
            'Tecnicas'     => [],
            'Economicas'   => [],
            'PorAdjudicar' => [],
            'AprobacionesPendientes' => []
        ];
        $breadcrumbs = [];
    
        try {
            if (!isOfferer()) {
                if (isCustomer()) {
                    $id = User()->id;
                    $customercCompanyID = User()->customer_company_id;

                    // INVITACION PENDIENTE
                    $concursosInvitacionPendiente = $this->getConcursoBaseQueryPorUsuario()
                        ->whereHas('oferentes', function ($oferentes) {
                            $oferentes->where('etapa_actual', Participante::ETAPAS['invitacion-pendiente']);
                        })
                        ->get();

                    foreach ($concursosInvitacionPendiente as $concurso) {
                        $list['Invitaciones'][] = [
                            'id'     => $concurso->id,
                            'nombre' => $concurso->nombre,
                            'fecha'  => $concurso->fecha_limite ? $concurso->fecha_limite->format('Y-m-d') : date('Y-m-d'),
                            'class'  => 'invitacion-color',
                            'etapa'  => Participante::ETAPAS_NOMBRES['invitacion-pendiente'],
                            'tipo_concurso'  => $concurso->tipo_concurso,
                        ];
                    }

                    // CONSULTAS
                    $concursosMuroDeConsultas = $this->getConcursoBaseQueryPorUsuario()
                        ->whereHas('oferentes', function ($oferentes) {
                            $oferentes->where('etapa_actual', '!=', Participante::ETAPAS['seleccionado']);
                        })
                        ->get();

                    foreach ($concursosMuroDeConsultas as $concurso) {
                        $list['Consultas'][] = [
                            'id'     => $concurso->id,
                            'nombre' => $concurso->nombre,
                            'fecha'  => $concurso->finalizacion_consultas ? $concurso->finalizacion_consultas->format('Y-m-d') : date('Y-m-d'),
                            'class'  => 'muro-color',
                            'etapa'  => 'Finalizacion Muro de Consulta',
                            'tipo_concurso'  => $concurso->tipo_concurso,
                        ];
                    }

                    
                    // TECNICA (para todos los tipos de usuarios)
                    $etapasTecnicas = [
                        'tecnica-pendiente',
                        'tecnica-pendiente-2',
                        'tecnica-pendiente-3',
                        'tecnica-pendiente-4',
                        'tecnica-pendiente-5',
                        'tecnica-presentada',
                        'tecnica-presentada-2',
                        'tecnica-presentada-3',
                        'tecnica-presentada-4',
                        'tecnica-presentada-5'
                    ];

                    $concursosTecnicaPendiente = $this->getConcursoBaseQueryPorUsuario()
                        ->whereRaw("LOWER(ficha_tecnica_incluye) = 'si'")
                        ->whereHas('oferentes', function ($oferentes) use ($etapasTecnicas) {
                            $oferentes->whereIn('etapa_actual', $etapasTecnicas)
                                    ->where('rechazado', '0');
                        })
                        ->get();


                    foreach ($concursosTecnicaPendiente as $concurso) {
                        $list['Tecnicas'][] = [
                            'id'     => $concurso->id,
                            'nombre' => $concurso->nombre,
                            'fecha'  => $concurso->ficha_tecnica_fecha_limite ? $concurso->ficha_tecnica_fecha_limite->format('Y-m-d') : date('Y-m-d'),
                            'class'  => 'tecnica-color',
                            'etapa'  => 'Evaluación Técnica',
                            'tipo_concurso'  => $concurso->tipo_concurso,
                        ];
                    }


                    // ECONOMICA
                    $concursosEconomicaPendiente = $this->getConcursoBaseQueryPorUsuario()
                        ->where(function ($query) {
                            $query->where('tipo_concurso', 'sobrecerrado')
                                  ->orWhere('tipo_concurso', 'online');
                        })
                        ->whereHas('oferentes', function ($oferentes) {
                            $oferentes->whereIn('etapa_actual', [
                                'economica-presentada',
                                'economica-pendiente',
                                'economica-pendiente-1',
                                'economica-pendiente-2',
                                'economica-pendiente-3',
                                'economica-pendiente-4',
                                'economica-pendiente-5'
                            ])
                            ->where('rechazado', '0');
                        })
                        ->get();

                    foreach ($concursosEconomicaPendiente as $concurso) {
                        $fechaEconomica = $concurso->is_online
                            ? ($concurso->inicio_subasta ? $concurso->inicio_subasta->format('Y-m-d') : date('Y-m-d'))
                            : ($concurso->fecha_limite_economicas ? $concurso->fecha_limite_economicas->format('Y-m-d') : date('Y-m-d'));
                        
                        $list['Economicas'][] = [
                            'id'     => $concurso->id,
                            'nombre' => $concurso->nombre,
                            'fecha'  => $fechaEconomica,
                            'class'  => 'economica-color',
                            'etapa'  => Participante::ETAPAS_NOMBRES['economica-pendiente'],
                            'tipo_concurso'  => $concurso->tipo_concurso,
                        ];
                    }

                    // ADJUDICACION
                    $concursoAdjudicacionPendiente = $this->getConcursoBaseQueryPorUsuario()
                        ->whereHas('oferentes', function ($oferentes) {
                            $oferentes->where('etapa_actual', Participante::ETAPAS['adjudicacion-pendiente'])
                                      ->where('rechazado', '0');
                        })
                        ->get();

                    foreach ($concursoAdjudicacionPendiente as $concurso) {
                        $list['PorAdjudicar'][] = [
                            'id'     => $concurso->id,
                            'nombre' => $concurso->nombre,
                            'fecha'  => $concurso->fecha_limite ? $concurso->fecha_limite->format('Y-m-d') : date('Y-m-d'),
                            'class'  => 'adjudicacion-color',
                            'etapa'  => Participante::ETAPAS_NOMBRES['adjudicacion-pendiente'],
                            'tipo_concurso'  => $concurso->tipo_concurso,
                        ];
                    }

                    // APROBACIONES PENDIENTES - Para usuarios que son aprobadores en la cadena
                    $userId = User()->id;
                    
                    // Obtener los contest_ids únicos con aprobaciones pendientes
                    $contestIds = AdjudicationApproval::where('status', 'pending')
                        ->distinct()
                        ->pluck('contest_id');

                    foreach ($contestIds as $contestId) {
                        // Obtener el PRIMER pendiente por sort_order para este concurso
                        $nextPending = AdjudicationApproval::where('contest_id', $contestId)
                            ->where('status', 'pending')
                            ->orderBy('sort_order', 'asc')
                            ->first();

                        // Solo mostrar si el user_id del siguiente pendiente coincide con el usuario actual
                        if ($nextPending && $nextPending->user_id && $nextPending->user_id == $userId) {
                            $concurso = Concurso::find($contestId);
                            if ($concurso) {
                                // Usar la fecha de fin de económica o fecha límite
                                $fechaEconomica = $concurso->fecha_limite_economicas 
                                    ? $concurso->fecha_limite_economicas->format('Y-m-d') 
                                    : ($concurso->fecha_limite ? $concurso->fecha_limite->format('Y-m-d') : date('Y-m-d'));

                                $list['AprobacionesPendientes'][] = [
                                    'id'     => $concurso->id,
                                    'nombre' => $concurso->nombre,
                                    'fecha'  => $fechaEconomica,
                                    'class'  => 'aprobacion-pendiente-color',
                                    'etapa'  => 'Aprobación Pendiente',
                                    'tipo_concurso'  => $concurso->tipo_concurso,
                                ];
                            }
                        }
                    }
                }

            /// BEGIN OFFERER ///
            } else {

                $offererCompanyID = User()->offerer_company_id;

                //INVITACION PENDIENTE
                $concursosInvitacionPendiente = Concurso::where([
                    ['deleted_at', '=', null]
                ])
                ->whereHas('oferentes', function ($oferentes) use ($offererCompanyID){
                    $oferentes
                        ->where([
                            ['id_offerer', '=', $offererCompanyID],
                            ['etapa_actual', '=', Participante::ETAPAS['invitacion-pendiente']],
                            ['rechazado', '=', '0']
                        ]);
                })
                ->get();                

                foreach ($concursosInvitacionPendiente as $concurso) {

                    $list['Invitaciones'][] = [
                        'id'     => $concurso->id,
                        'nombre' => $concurso->nombre,
                        'fecha'  => $concurso->fecha_limite ? $concurso->fecha_limite->format('Y-m-d') : date('Y-m-d'),
                        'class'  => 'invitacion-color',
                        'etapa'  => Participante::ETAPAS_NOMBRES['invitacion-pendiente'],
                        'tipo_concurso'  => $concurso->tipo_concurso,
                    ];
                }

                // FINALIZA MURO CONSULTA
                $concursosMuroDeConsultas = Concurso::where([
                    ['deleted_at', '=', null]
                ])
                ->whereHas('oferentes', function ($oferentes) use ($offererCompanyID) {
                    $oferentes->where('id_offerer', '=', $offererCompanyID)
                            ->where('rechazado', '=', '0')
                            ->whereNotIn('etapa_actual', array_merge(
                                Participante::ETAPAS_RECHAZADAS,
                                [Participante::ETAPAS['seleccionado']]
                            ));
                })
                ->get();
                
                foreach ($concursosMuroDeConsultas as $concurso) {

                    $list['Consultas'][] = [
                        'id'     => $concurso->id,
                        'nombre' => $concurso->nombre,
                        'fecha'  => $concurso->finalizacion_consultas ? $concurso->finalizacion_consultas->format('Y-m-d') : date('Y-m-d'),
                        'class'  => 'muro-color',
                        'etapa'  => 'Finalizacion Muro de Consulta',
                        'tipo_concurso'  => $concurso->tipo_concurso,
                    ];
                }


                //TÉCNICA PENDIENTE
                $concursosTecnicaPendiente = Concurso::where([
                    ['deleted_at', '=', null]
                ])
                ->whereHas('oferentes', function ($oferentes) use ($offererCompanyID){
                    $oferentes
                        ->where([
                            ['id_offerer', '=', $offererCompanyID],
                            ['rechazado', '=', '0'],
                            ['etapa_actual', '=', Participante::ETAPAS['tecnica-pendiente']]
                        ]);
                })
                ->get();

                foreach ($concursosTecnicaPendiente as $concurso) {

                    $list['Tecnicas'][] = [
                        'id'     => $concurso->id,
                        'nombre' => $concurso->nombre,
                        'fecha'  => $concurso->ficha_tecnica_fecha_limite ? $concurso->ficha_tecnica_fecha_limite->format('Y-m-d') : date('Y-m-d'),
                        'class'  => 'tecnica-color',
                        'etapa'  => Participante::ETAPAS_NOMBRES['tecnica-pendiente'],
                        'tipo_concurso'  => $concurso->tipo_concurso,
                    ];
                }


                //ECONOMICA PENDIENTE
                $concursosEconomicaPendiente = Concurso::where([
                    ['deleted_at', '=', null]
                ])
                ->where(function ($query) {
                    $query->where('tipo_concurso', 'sobrecerrado')
                          ->orWhere('tipo_concurso', 'online');
                })
                ->whereHas('oferentes', function ($oferentes) use ($offererCompanyID){
                    $oferentes->whereIn('etapa_actual', [
                        'economica-presentada',
                        'economica-pendiente',
                        'economica-pendiente-1',
                        'economica-pendiente-2',
                        'economica-pendiente-3',
                        'economica-pendiente-4',
                        'economica-pendiente-5'
                    ])
                    ->where([
                        ['id_offerer', '=', $offererCompanyID],
                        ['rechazado', '=', '0']
                    ]);
                })
                ->get();

                foreach ($concursosEconomicaPendiente as $concurso) {
                    $fechaEconomica = $concurso->is_online
                        ? ($concurso->inicio_subasta ? $concurso->inicio_subasta->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'))
                        : ($concurso->fecha_limite_economicas ? $concurso->fecha_limite_economicas->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'));

                    $list['Economicas'][] = [
                        'id'     => $concurso->id,
                        'nombre' => $concurso->nombre,
                        'fecha'  => $fechaEconomica,
                        'class'  => 'economica-color',
                        'etapa'  => Participante::ETAPAS_NOMBRES['economica-pendiente'],
                        'tipo_concurso'  => $concurso->tipo_concurso,
                    ];
                }


                //ADJUDICACION PENDIENTE
                $concursoAdjudicacionPendiente = Concurso::where([
                    ['deleted_at', '=', null]
                ])
                ->whereHas('oferentes', function ($oferentes) use ($offererCompanyID){
                    $oferentes
                        ->where([
                            ['id_offerer', '=', $offererCompanyID],
                            ['rechazado', '=', '0'],
                            ['etapa_actual', '=', Participante::ETAPAS['adjudicacion-pendiente']]
                        ]);
                })
                ->get();

                foreach ($concursoAdjudicacionPendiente as $concurso) {

                    $list['PorAdjudicar'][] = [
                        'id'     => $concurso->id,
                        'nombre' => $concurso->nombre,
                        'fecha'  => $concurso->fecha_limite ? $concurso->fecha_limite->format('Y-m-d') : date('Y-m-d'),
                        'class'  => 'adjudicacion-color',
                        'etapa'  => Participante::ETAPAS_NOMBRES['adjudicacion-pendiente'],
                        'tipo_concurso'  => $concurso->tipo_concurso,
                    ];
                }
            }

            $success = true;

            $breadcrumbs = [
                ['description' => 'Dashboard', 'url' => null]
            ];
        } catch (\Exception $e) {
            dd($e);
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success'   => $success,
            'message'   => $message,
            'data'      => [
                'list'          => $list,
                'breadcrumbs'   => $breadcrumbs
            ]
        ], $status);
    }

    private function getConcursoBaseQueryPorUsuario()
{
    $user = User();
    $query = Concurso::whereNull('deleted_at');

    if ($user->type_id == 8 || $user->type_id == 5  ) {
        // Supervisor: ver todos los concursos de su empresa
        $idsUsuariosEmpresa = User::where('customer_company_id', $user->customer_company_id)->pluck('id');
        $query->whereIn('id_cliente', $idsUsuariosEmpresa);

    } else if ($user->type_id == 4) {
        // Evaluador técnico
        $etapasTecnicas = [
            'tecnica-pendiente',
            'tecnica-pendiente-2',
            'tecnica-pendiente-3',
            'tecnica-pendiente-4',
            'tecnica-pendiente-5',
            'tecnica-presentada',
            'tecnica-presentada-2',
            'tecnica-presentada-3',
            'tecnica-presentada-4',
            'tecnica-presentada-5'
        ];

        $query->where('ficha_tecnica_usuario_evalua', $user->id)
              ->whereRaw("LOWER(ficha_tecnica_incluye) = 'si'")
              ->whereHas('oferentes', function ($q) use ($etapasTecnicas) {
                  $q->whereIn('etapa_actual', $etapasTecnicas)
                    ->where('rechazado', '0');
              });

    } else {
        // Cliente común
        $query->where('id_cliente', $user->id);
    }

    return $query;
}



}
