<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\AdjudicationApproval;
use App\Models\Concurso;
use App\Models\EstrategiaLiberacion;
use App\Models\User;
use App\Models\Tipocambio;
use App\Services\EmailService;

class ApprovalController extends BaseController
{
    /**
     * Get pending approvals for current user
     */
    public function getMyPending(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = [];

        try {
            $user = user();
            
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
                if ($nextPending && $nextPending->user_id && $nextPending->user_id == $user->id) {
                    $contest = Concurso::find($contestId);
                    if ($contest) {
                        $requester = User::find($nextPending->requester_user_id);
                        
                        $data[] = [
                            'ContestId' => $nextPending->contest_id,
                            'ContestName' => $contest->nombre,
                            'AdjudicationType' => ucfirst($nextPending->adjudication_type),
                            'Amount' => number_format($nextPending->amount, 2, ',', '.'),
                            'AmountUsd' => number_format($nextPending->amount_usd, 2, ',', '.'),
                            'Role' => $nextPending->role,
                            'RequesterName' => $requester ? $requester->first_name . ' ' . $requester->last_name : '-',
                            'CreatedAt' => $nextPending->created_at ? $nextPending->created_at->format('d-m-Y H:i') : '-',
                            'TipoConcursoPath' => $contest->tipo_concurso ?? 'sobrecerrado'
                        ];
                    }
                }
            }

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Generate session token for approver to access contest
     */
    public function generateToken(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $user = user();
            $contestId = $request->getParsedBody()['contest_id'] ?? null;

            if (!$contestId) {
                throw new \Exception('ID del concurso requerido');
            }

            // Verify user is an approver for this contest (solo por user_id)
            $isApprover = AdjudicationApproval::where('contest_id', $contestId)
                ->where('status', 'pending')
                ->where('user_id', $user->id)
                ->exists();

            if (!$isApprover) {
                throw new \Exception('No autorizado para aprobar este concurso');
            }

            // Generate token
            $secret = getenv('TOKEN_SECRET_KEY');
            $sessionId = session_id();
            $token = hash_hmac('sha256', $contestId . $sessionId, $secret);

            $_SESSION['edit_token'] = $_SESSION['edit_token'] ?? [];
            $_SESSION['edit_token'][$contestId] = $token;

            $success = true;
            $message = 'Token generado';

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }

    /**
     * Iniciar el proceso de aprobación - SIMPLIFICADO
     * Los user_id ya vienen definidos desde EstrategiaController
     */
    public function startApproval(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;

        try {
            $user = user();
            $body = json_decode($request->getParsedBody()['Data']);

            if (empty($body)) {
                throw new \Exception('No se recibieron datos');
            }

            $contestId = $body->IdConcurso;
            $adjudicationType = $body->Type;
            $amount = (float) $body->Monto;
            $comment = $body->Comment ?? '';
            $adjudicationData = $body->Data ?? null;

            $contest = Concurso::find($contestId);
            if (!$contest) {
                throw new \Exception('Concurso no encontrado');
            }

            // Verificar si ya existe una solicitud pendiente activa (no rechazada)
            // Una cadena rechazada permite iniciar una nueva
            $currentBatch = AdjudicationApproval::getCurrentBatchId($contestId);
            $existing = AdjudicationApproval::where('contest_id', $contestId)
                ->where('batch_id', $currentBatch)
                ->where('status', AdjudicationApproval::STATUS_PENDING)
                ->first();
            
            // Solo bloquear si hay pendientes Y la cadena actual no está rechazada
            $isCurrentBatchRejected = AdjudicationApproval::isChainRejected($contestId);
            
            if ($existing && !$isCurrentBatchRejected) {
                throw new \Exception('Ya existe una solicitud de aprobación para este concurso');
            }

            // Obtener estrategia de liberación
            $strategy = EstrategiaLiberacion::getByCompany($user->customer_company_id);
            if (!$strategy || !$strategy->habilitado) {
                throw new \Exception('La estrategia de liberación no está habilitada');
            }

            // Convertir monto a USD
            $amountUsd = $this->convertToUsd($amount, $contest->moneda);
            
            // Construir niveles de aprobación (esto ya incluye los user_id)
            $approvalLevels = $this->buildApprovalLevels(
                $strategy,
                $user->customer_company_id,
                $contest->area_sol,
                $amountUsd
            );

            if (empty($approvalLevels)) {
                $data = [
                    'requires_approval' => false,
                    'message' => 'Amount does not require approval according to configured strategy'
                ];
                $success = true;
                return $this->json($response, [
                    'success' => $success,
                    'message' => $message,
                    'data' => $data
                ], $status);
            }

            // Crear registros de aprobación
            $records = AdjudicationApproval::createApprovalRequest(
                $contestId,
                $adjudicationType,
                $amount,
                $amountUsd,
                $approvalLevels,
                json_decode(json_encode($adjudicationData), true),
                $comment,
                $user->id
            );

            // Obtener el primer aprobador y su email
            $firstApproval = $records[0];
            $firstApproverEmail = null;
            $firstApproverName = null;
            
            if ($firstApproval->user_id) {
                $firstApprover = User::find($firstApproval->user_id);
                if ($firstApprover) {
                    $firstApproverEmail = $firstApprover->email;
                    $firstApproverName = $firstApprover->first_name . ' ' . $firstApprover->last_name;
                }
            }

            // Enviar notificación al primer aprobador
            $emailSent = false;
            if ($firstApproverEmail) {
                $emailSent = $this->sendApprovalEmail($firstApproval, $contest, $firstApproverEmail, $firstApproverName);
            }

            $data = [
                'requires_approval' => true,
                'levels' => array_map(function($r) {
                    return $r->toResponseArray();
                }, $records),
                'message' => 'Solicitud de aprobación creada.',
                // DEBUG: Información para ver en consola
                'debug' => [
                    'first_approver_user_id' => $firstApproval->user_id,
                    'first_approver_email' => $firstApproverEmail,
                    'first_approver_name' => $firstApproverName,
                    'email_sent' => $emailSent,
                    'email_error' => $this->lastEmailError
                ]
            ];

            $success = true;
            $message = 'Proceso de aprobación solicitado correctamente';

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Obtener estado de aprobación de un concurso
     */
    public function getStatus(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;

        try {
            $user = user();
            $contestId = $params['contest_id'] ?? null;

            if (!$contestId) {
                throw new \Exception('ID del concurso requerido');
            }

            $approvals = AdjudicationApproval::getByContest($contestId);
            
            if ($approvals->isEmpty()) {
                // Verificar si hay historial de cadenas rechazadas anteriores
                $rejectedHistory = AdjudicationApproval::getRejectedHistory($contestId);
                
                $data = [
                    'has_request' => false,
                    'levels' => [],
                    'can_approve' => false,
                    'chain_complete' => false,
                    'chain_rejected' => false,
                    'is_chain_approver' => false,
                    'rejected_history' => $this->formatRejectedHistory($rejectedHistory)
                ];
            } else {
                // Verificar si el usuario actual puede aprobar (simplemente si su user_id coincide con el nivel pendiente)
                $pendingApproval = $approvals->where('status', 'pending')->first();
                $canApprove = $pendingApproval && $pendingApproval->user_id == $user->id;
                
                // Verificar si el usuario pertenece a la cadena de aprobación (en cualquier nivel)
                $isChainApprover = $approvals->where('user_id', $user->id)->isNotEmpty();
                
                $first = $approvals->first();
                
                // Obtener historial de cadenas rechazadas anteriores
                $rejectedHistory = AdjudicationApproval::getRejectedHistory($contestId);
                
                $data = [
                    'has_request' => true,
                    'adjudication_type' => $first->adjudication_type,
                    'amount' => (float) $first->amount,
                    'amount_usd' => (float) $first->amount_usd,
                    'levels' => $approvals->map(function($a) {
                        return $a->toResponseArray();
                    })->toArray(),
                    'can_approve' => $canApprove,
                    'pending_approval_id' => $pendingApproval ? $pendingApproval->id : null,
                    'chain_complete' => AdjudicationApproval::isChainComplete($contestId),
                    'chain_rejected' => AdjudicationApproval::isChainRejected($contestId),
                    'requester_user_id' => $first->requester_user_id,
                    'is_chain_approver' => $isChainApprover,
                    'rejected_history' => $this->formatRejectedHistory($rejectedHistory)
                ];
            }

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Aprobar un nivel - SIMPLIFICADO
     */
    public function approve(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;

        try {
            $user = user();
            $body = json_decode($request->getParsedBody()['Data']);

            $contestId = $body->contest_id ?? null;
            $reason = $body->reason ?? null;

            if (!$contestId) {
                throw new \Exception('ID del concurso requerido');
            }

            // Buscar el nivel pendiente que corresponde a este usuario
            $approval = AdjudicationApproval::where('contest_id', $contestId)
                ->where('status', 'pending')
                ->where('user_id', $user->id)
                ->orderBy('sort_order', 'asc')
                ->first();
            
            if (!$approval) {
                throw new \Exception('Sin permiso para aprobar o no hay niveles pendientes');
            }

            // Aprobar
            $approval->approve($user->id, $reason);

            // Buscar el siguiente nivel pendiente
            $next = AdjudicationApproval::where('contest_id', $contestId)
                ->where('status', 'pending')
                ->orderBy('sort_order', 'asc')
                ->first();
            
            $nextApproverEmail = null;
            $nextApproverName = null;
            
            if ($next) {
                // Obtener email del siguiente aprobador
                if ($next->user_id) {
                    $nextApprover = User::find($next->user_id);
                    if ($nextApprover) {
                        $nextApproverEmail = $nextApprover->email;
                        $nextApproverName = $nextApprover->first_name . ' ' . $nextApprover->last_name;
                        
                        // Enviar notificación
                        $contest = Concurso::find($contestId);
                        $this->sendApprovalEmail($next, $contest, $nextApproverEmail, $nextApproverName);
                    }
                }
                $message = 'Aprobación registrada. Se ha notificado al siguiente aprobador.';
            } else {
                // Cadena completa - notificar al solicitante
                $message = 'Aprobación registrada. La cadena de aprobación está completa.';
                $this->notifyChainComplete($approval);
            }

            $approvals = AdjudicationApproval::getByContest($contestId);
            
            $data = [
                'levels' => $approvals->map(function($a) {
                    return $a->toResponseArray();
                })->toArray(),
                'chain_complete' => AdjudicationApproval::isChainComplete($contestId),
                // DEBUG
                'debug' => [
                    'next_approver_email' => $nextApproverEmail,
                    'next_approver_name' => $nextApproverName
                ]
            ];

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Rechazar un nivel
     */
    public function reject(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;

        try {
            $user = user();
            $body = json_decode($request->getParsedBody()['Data']);

            $contestId = $body->contest_id ?? null;
            $reason = $body->reason ?? null;

            if (!$contestId) {
                throw new \Exception('ID del concurso requerido');
            }

            if (empty($reason)) {
                throw new \Exception('El motivo del rechazo es obligatorio');
            }

            // Buscar el nivel pendiente que corresponde a este usuario
            $approval = AdjudicationApproval::where('contest_id', $contestId)
                ->where('status', 'pending')
                ->where('user_id', $user->id)
                ->orderBy('sort_order', 'asc')
                ->first();
            
            if (!$approval) {
                throw new \Exception('No tiene permiso para rechazar esta adjudicación');
            }

            $approval->reject($user->id, $reason);
            $this->notifyRejection($approval);

            $approvals = AdjudicationApproval::getByContest($contestId);
            
            $data = [
                'levels' => $approvals->map(function($a) {
                    return $a->toResponseArray();
                })->toArray(),
                'chain_rejected' => true
            ];

            $success = true;
            $message = 'Adjudicación rechazada. Se ha notificado al solicitante.';

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Procesar adjudicación después de aprobación completa
     */
    public function processAdjudication(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;

        try {
            $body = json_decode($request->getParsedBody()['Data']);
            $contestId = $body->contest_id ?? null;

            if (!$contestId) {
                throw new \Exception('ID del concurso requerido');
            }

            if (!AdjudicationApproval::isChainComplete($contestId)) {
                throw new \Exception('La cadena de aprobación no está completa');
            }

            $approval = AdjudicationApproval::where('contest_id', $contestId)->first();
            
            if (!$approval) {
                throw new \Exception('Solicitud de aprobación no encontrada');
            }

            $data = [
                'adjudication_type' => $approval->adjudication_type,
                'adjudication_data' => $approval->adjudication_data,
                'comment' => $approval->adjudication_comment,
                'can_process' => true
            ];

            $success = true;
            $message = 'La adjudicación puede ser procesada';

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Convertir monto a USD
     */
    private function convertToUsd($amount, $currencyId)
    {
        if (empty($currencyId)) {
            return (float) $amount;
        }

        $exchangeRate = Tipocambio::where('monedaId', $currencyId)->first();
        
        if ($exchangeRate && $exchangeRate->cambio) {
            $rateStr = str_replace('.', '', $exchangeRate->cambio);
            $rateStr = str_replace(',', '.', $rateStr);
            $rateFloat = (float) $rateStr;
            
            if ($rateFloat > 0) {
                return (float) $amount / $rateFloat;
            }
        }
        
        return (float) $amount;
    }

    /**
     * Construir niveles de aprobación con user_id
     */
    private function buildApprovalLevels($strategy, $customerCompanyId, $requesterArea, $amountUsd)
    {
        $levels = [];
        $order = 1;

        $thresholdLevel1 = (float) $strategy->monto_nivel_1;
        $thresholdLevel2 = (float) $strategy->monto_nivel_2;
        $thresholdLevel3 = (float) $strategy->monto_nivel_3;

        if ($strategy->jefe_compras && $amountUsd > $thresholdLevel1) {
            $user = $this->findUserByRoleArea($customerCompanyId, 'Jefe', 'Compras');
            $levels[] = [
                'sort_order' => $order++,
                'level' => 1,
                'role' => 'Jefe de Compras',
                'area' => 'Compras',
                'threshold_amount' => $thresholdLevel1,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
                'user_email' => $user ? $user->email : null
            ];
        }

        if ($strategy->jefe_solicitante && $amountUsd > $thresholdLevel1 && $requesterArea) {
            $user = $this->findUserByRoleArea($customerCompanyId, 'Jefe', $requesterArea);
            $levels[] = [
                'sort_order' => $order++,
                'level' => 1,
                'role' => 'Jefe de ' . $requesterArea,
                'area' => $requesterArea,
                'threshold_amount' => $thresholdLevel1,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
                'user_email' => $user ? $user->email : null
            ];
        }

        if ($strategy->gerente_compras && $amountUsd > $thresholdLevel2) {
            $user = $this->findUserByRoleArea($customerCompanyId, 'Gerente', 'Compras');
            $levels[] = [
                'sort_order' => $order++,
                'level' => 2,
                'role' => 'Gerente de Compras',
                'area' => 'Compras',
                'threshold_amount' => $thresholdLevel2,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
                'user_email' => $user ? $user->email : null
            ];
        }

        if ($strategy->gerente_solicitante && $amountUsd > $thresholdLevel2 && $requesterArea) {
            $user = $this->findUserByRoleArea($customerCompanyId, 'Gerente', $requesterArea);
            $levels[] = [
                'sort_order' => $order++,
                'level' => 2,
                'role' => 'Gerente de ' . $requesterArea,
                'area' => $requesterArea,
                'threshold_amount' => $thresholdLevel2,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
                'user_email' => $user ? $user->email : null
            ];
        }

        if ($strategy->gerente_general && $amountUsd > $thresholdLevel3) {
            $user = $this->findUserByRoleArea($customerCompanyId, 'Gerente General');
            $levels[] = [
                'sort_order' => $order++,
                'level' => 3,
                'role' => 'Gerente General',
                'area' => null,
                'threshold_amount' => $thresholdLevel3,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
                'user_email' => $user ? $user->email : null
            ];
        }

        return $levels;
    }

    /**
     * Buscar usuario por rol y área - retorna el objeto User completo
     */
    private function findUserByRoleArea($customerCompanyId, $role, $area = null)
    {
        $query = User::where('customer_company_id', $customerCompanyId)
            ->where('rol', $role)
            ->whereNull('deleted_at');
        
        if ($area) {
            $query->where('area', $area);
        }
        
        return $query->first();
    }

    /**
     * Enviar email de aprobación pendiente - SIMPLIFICADO
     */
    private function sendApprovalEmail($approval, $contest, $email, $name)
    {
        // Store error for debugging
        $this->lastEmailError = null;
        
        try {
            if (!$email) {
                $this->lastEmailError = 'No email provided';
                return false;
            }

            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/approval-pending.tpl';
            
            if (!file_exists($template)) {
                $this->lastEmailError = 'Template not found: ' . $template;
                return false;
            }
            
            $subject = $contest->nombre . ' - Adjudicación pendiente de aprobación';
            
            $user = new \stdClass();
            $user->full_name = $name;
            $user->email = $email;
            
            $html = $this->fetch($template, [
                'title' => 'Adjudicación pendiente de aprobación',
                'ano' => Carbon::now()->format('Y'),
                'app_url' => env('APP_URL'),
                'concurso' => $contest,
                'approval' => $approval,
                'user' => $user
            ]);
            
            if (empty($html)) {
                $this->lastEmailError = 'Template rendered empty HTML';
                return false;
            }
            
            $result = $emailService->send($html, $subject, [$email], $name);
            
            if (!$result) {
                $this->lastEmailError = 'EmailService->send() returned false';
            }
            
            return $result ? true : false;

        } catch (\Exception $e) {
            $this->lastEmailError = $e->getMessage();
            return false;
        }
    }
    
    private $lastEmailError = null;

    /**
     * Notificar cadena completa al solicitante
     */
    private function notifyChainComplete($approval)
    {
        try {
            $requester = User::find($approval->requester_user_id);
            if (!$requester || !$requester->email) {
                return;
            }

            $contest = Concurso::find($approval->contest_id);
            
            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/approval-chain-complete.tpl';
            
            $subject = $contest->nombre . ' - Cadena de aprobación completada';
            
            $html = $this->fetch($template, [
                'title' => 'Cadena de aprobación completada',
                'ano' => Carbon::now()->format('Y'),
                'app_url' => env('APP_URL'),
                'concurso' => $contest,
                'approval' => $approval,
                'user' => $requester
            ]);
            
            $emailService->send($html, $subject, [$requester->email], $requester->full_name);

        } catch (\Exception $e) {
            // Silenciar error
        }
    }

    /**
     * Notificar rechazo al solicitante y a los aprobadores anteriores
     */
    private function notifyRejection($approval)
    {
        try {
            $contest = Concurso::find($approval->contest_id);
            if (!$contest) {
                return;
            }
            
            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/approval-rejected.tpl';
            $subject = $contest->nombre . ' - Adjudicación rechazada';
            
            // Lista de destinatarios (evitar duplicados)
            $recipients = [];
            
            // 1. Siempre notificar al solicitante
            $requester = User::find($approval->requester_user_id);
            if ($requester && $requester->email) {
                $recipients[$requester->id] = [
                    'email' => $requester->email,
                    'name' => $requester->full_name,
                    'user' => $requester
                ];
            }
            
            // 2. Obtener aprobadores anteriores que ya aprobaron (del mismo batch)
            $previousApprovers = AdjudicationApproval::where('contest_id', $approval->contest_id)
                ->where('batch_id', $approval->batch_id)
                ->where('status', AdjudicationApproval::STATUS_APPROVED)
                ->where('sort_order', '<', $approval->sort_order)
                ->orderBy('sort_order', 'asc')
                ->get();
            
            foreach ($previousApprovers as $prevApproval) {
                // Usar el user_id del aprobador (quien aprobó, no el asignado originalmente)
                $approverId = $prevApproval->approved_by_user_id ?? $prevApproval->user_id;
                if ($approverId && !isset($recipients[$approverId])) {
                    $approver = User::find($approverId);
                    if ($approver && $approver->email) {
                        $recipients[$approverId] = [
                            'email' => $approver->email,
                            'name' => $approver->full_name,
                            'user' => $approver
                        ];
                    }
                }
            }
            
            // 3. Enviar email a cada destinatario
            foreach ($recipients as $recipient) {
                $html = $this->fetch($template, [
                    'title' => 'Adjudicación rechazada',
                    'ano' => Carbon::now()->format('Y'),
                    'app_url' => env('APP_URL'),
                    'concurso' => $contest,
                    'approval' => $approval,
                    'user' => $recipient['user'],
                    'reason' => $approval->reason
                ]);
                
                $emailService->send($html, $subject, [$recipient['email']], $recipient['name']);
            }

        } catch (\Exception $e) {
            // Silenciar error
        }
    }

    /**
     * Formatear historial de cadenas rechazadas para la respuesta
     */
    private function formatRejectedHistory($rejectedHistory)
    {
        if ($rejectedHistory->isEmpty()) {
            return [];
        }

        $formatted = [];
        
        foreach ($rejectedHistory as $batchId => $approvals) {
            $first = $approvals->first();
            $rejected = $approvals->where('status', 'rejected')->first();
            
            $formatted[] = [
                'batch_id' => $batchId,
                'adjudication_type' => $first->adjudication_type,
                'amount' => (float) $first->amount,
                'amount_usd' => (float) $first->amount_usd,
                'rejected_by' => $rejected ? $rejected->user_name : null,
                'rejected_at' => $rejected && $rejected->response_date ? $rejected->response_date->format('d/m/Y H:i') : null,
                'rejection_reason' => $rejected ? $rejected->reason : null,
                'rejected_at_level' => $rejected ? $rejected->role : null,
                'levels' => $approvals->map(function($a) {
                    return $a->toResponseArray();
                })->values()->toArray()
            ];
        }

        return $formatted;
    }
}
