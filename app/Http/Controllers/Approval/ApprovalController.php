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
            
            $pendingApprovals = AdjudicationApproval::where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($pendingApprovals as $approval) {
                // Check if user can approve this one
                if ($this->userCanApprove($approval, $user)) {
                    $contest = Concurso::find($approval->contest_id);
                    if ($contest) {
                        $requester = User::find($approval->requester_user_id);
                        
                        $data[] = [
                            'ContestId' => $approval->contest_id,
                            'ContestName' => $contest->nombre,
                            'AdjudicationType' => ucfirst($approval->adjudication_type),
                            'Amount' => number_format($approval->amount, 2, ',', '.'),
                            'AmountUsd' => number_format($approval->amount_usd, 2, ',', '.'),
                            'Role' => $approval->role,
                            'RequesterName' => $requester ? $requester->first_name . ' ' . $requester->last_name : '-',
                            'CreatedAt' => $approval->created_at ? $approval->created_at->format('d-m-Y H:i') : '-',
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
                throw new \Exception('Contest ID required');
            }

            // Verify user is an approver for this contest
            $isApprover = AdjudicationApproval::where('contest_id', $contestId)
                ->where('status', 'pending')
                ->get()
                ->contains(function($approval) use ($user) {
                    return $this->userCanApprove($approval, $user);
                });

            if (!$isApprover) {
                throw new \Exception('Not authorized to approve this contest');
            }

            // Generate token (same logic as ConcursoController)
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

    private function userCanApprove($approval, $user)
    {
        // Direct user match
        if ($approval->user_id && $approval->user_id == $user->id) {
            return true;
        }

        // Role-based match when user_id is null
        if (!$approval->user_id && $user->rol) {
            $userRoleString = $user->rol;
            if ($user->area) {
                $userRoleString .= ' de ' . $user->area;
            }

            if ($approval->role === $userRoleString) {
                return true;
            }

            if ($approval->role === $user->rol && empty($approval->area)) {
                return true;
            }
        }

        return false;
    }

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
                throw new \Exception('No data received');
            }

            $contestId = $body->IdConcurso;
            $adjudicationType = $body->Type;
            $amount = (float) $body->Monto;
            $comment = $body->Comment ?? '';
            $adjudicationData = $body->Data ?? null;

            $contest = Concurso::find($contestId);
            if (!$contest) {
                throw new \Exception('Contest not found');
            }

            $existing = AdjudicationApproval::where('contest_id', $contestId)
                ->where('status', AdjudicationApproval::STATUS_PENDING)
                ->first();
            
            if ($existing) {
                throw new \Exception('An approval request already exists for this contest');
            }

            $strategy = EstrategiaLiberacion::getByCompany($user->customer_company_id);
            if (!$strategy || !$strategy->habilitado) {
                throw new \Exception('Release strategy is not enabled');
            }

            $amountUsd = $this->convertToUsd($amount, $contest->moneda);
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

            $this->notifyApprover($records[0], $contest);

            $data = [
                'requires_approval' => true,
                'levels' => array_map(function($r) {
                    return $r->toResponseArray();
                }, $records),
                'message' => 'Solicitud de aprobación creada. Se ha notificado al primer aprobador.'
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
                throw new \Exception('Contest ID required');
            }

            $approvals = AdjudicationApproval::getByContest($contestId);
            
            if ($approvals->isEmpty()) {
                $data = [
                    'has_request' => false,
                    'levels' => [],
                    'can_approve' => false,
                    'chain_complete' => false,
                    'chain_rejected' => false
                ];
            } else {
                $canApprove = AdjudicationApproval::canApprove($contestId, $user->id);
                $first = $approvals->first();
                
                $data = [
                    'has_request' => true,
                    'adjudication_type' => $first->adjudication_type,
                    'amount' => (float) $first->amount,
                    'amount_usd' => (float) $first->amount_usd,
                    'levels' => $approvals->map(function($a) {
                        return $a->toResponseArray();
                    })->toArray(),
                    'can_approve' => $canApprove !== null,
                    'pending_approval_id' => $canApprove ? $canApprove->id : null,
                    'chain_complete' => AdjudicationApproval::isChainComplete($contestId),
                    'chain_rejected' => AdjudicationApproval::isChainRejected($contestId),
                    'requester_user_id' => $first->requester_user_id
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
                throw new \Exception('Contest ID required');
            }

            $approval = AdjudicationApproval::canApprove($contestId, $user->id);
            
            if (!$approval) {
                throw new \Exception('No permission to approve or no pending levels');
            }

            $approval->approve($user->id, $reason);

            $next = AdjudicationApproval::getNextPending($contestId);
            
            if ($next) {
                $contest = Concurso::find($contestId);
                $this->notifyApprover($next, $contest);
                $message = 'Aprobación registrada. Se ha notificado al siguiente aprobador.';
            } else {
                $message = 'Aprobación registrada. La cadena de aprobación está completa.';
                $this->notifyChainComplete($approval);
            }

            $approvals = AdjudicationApproval::getByContest($contestId);
            
            $data = [
                'levels' => $approvals->map(function($a) {
                    return $a->toResponseArray();
                })->toArray(),
                'chain_complete' => AdjudicationApproval::isChainComplete($contestId)
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
                throw new \Exception('Contest ID required');
            }

            if (empty($reason)) {
                throw new \Exception('Rejection reason is required');
            }

            $approval = AdjudicationApproval::canApprove($contestId, $user->id);
            
            if (!$approval) {
                throw new \Exception('No permission to reject');
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
            $message = 'Adjudication rejected. Requester has been notified.';

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

    public function cancel(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $user = user();
            $body = json_decode($request->getParsedBody()['Data']);

            $contestId = $body->contest_id ?? null;

            if (!$contestId) {
                throw new \Exception('Contest ID required');
            }

            $firstApproval = AdjudicationApproval::where('contest_id', $contestId)->first();
            
            if ($firstApproval && $firstApproval->requester_user_id != $user->id) {
                throw new \Exception('Sólo el solicitante original puede cancelar la solicitud de aprobación.');
            }

            AdjudicationApproval::cancelApprovals($contestId);

            $success = true;
            $message = 'Solicitud de aprobación cancelada';

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
                throw new \Exception('Contest ID required');
            }

            if (!AdjudicationApproval::isChainComplete($contestId)) {
                throw new \Exception('Approval chain is not complete');
            }

            $approval = AdjudicationApproval::where('contest_id', $contestId)->first();
            
            if (!$approval) {
                throw new \Exception('Approval request not found');
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

    private function buildApprovalLevels($strategy, $customerCompanyId, $requesterArea, $amountUsd)
    {
        $levels = [];
        $order = 1;

        $thresholdLevel1 = (float) $strategy->monto_nivel_1;
        $thresholdLevel2 = (float) $strategy->monto_nivel_2;
        $thresholdLevel3 = (float) $strategy->monto_nivel_3;

        if ($strategy->jefe_compras && $amountUsd > $thresholdLevel1) {
            $userName = $this->findUserByRoleArea($customerCompanyId, 'Jefe', 'Compras');
            $levels[] = [
                'sort_order' => $order++,
                'level' => 1,
                'role' => 'Jefe de Compras',
                'area' => 'Compras',
                'threshold_amount' => $thresholdLevel1,
                'user' => $userName
            ];
        }

        if ($strategy->jefe_solicitante && $amountUsd > $thresholdLevel1) {
            $role = $requesterArea ? 'Jefe de ' . $requesterArea : 'Jefe de Área Solicitante';
            $userName = $requesterArea 
                ? $this->findUserByRoleArea($customerCompanyId, 'Jefe', $requesterArea)
                : null;
            $levels[] = [
                'sort_order' => $order++,
                'level' => 1,
                'role' => $role,
                'area' => $requesterArea,
                'threshold_amount' => $thresholdLevel1,
                'user' => $userName
            ];
        }

        if ($strategy->gerente_compras && $amountUsd > $thresholdLevel2) {
            $userName = $this->findUserByRoleArea($customerCompanyId, 'Gerente', 'Compras');
            $levels[] = [
                'sort_order' => $order++,
                'level' => 2,
                'role' => 'Gerente de Compras',
                'area' => 'Compras',
                'threshold_amount' => $thresholdLevel2,
                'user' => $userName
            ];
        }

        if ($strategy->gerente_solicitante && $amountUsd > $thresholdLevel2) {
            $role = $requesterArea ? 'Gerente de ' . $requesterArea : 'Gerente de Área Solicitante';
            $userName = $requesterArea 
                ? $this->findUserByRoleArea($customerCompanyId, 'Gerente', $requesterArea)
                : null;
            $levels[] = [
                'sort_order' => $order++,
                'level' => 2,
                'role' => $role,
                'area' => $requesterArea,
                'threshold_amount' => $thresholdLevel2,
                'user' => $userName
            ];
        }

        if ($strategy->gerente_general && $amountUsd > $thresholdLevel3) {
            $userName = $this->findUserByRoleArea($customerCompanyId, 'Gerente General');
            $levels[] = [
                'sort_order' => $order++,
                'level' => 3,
                'role' => 'Gerente General',
                'area' => null,
                'threshold_amount' => $thresholdLevel3,
                'user' => $userName
            ];
        }

        return $levels;
    }

    private function findUserByRoleArea($customerCompanyId, $role, $area = null)
    {
        $query = User::where('customer_company_id', $customerCompanyId)
            ->where('rol', $role)
            ->whereNull('deleted_at');
        
        if ($area) {
            $query->where('area', $area);
        }
        
        $user = $query->first();
        return $user ? $user->first_name . ' ' . $user->last_name : null;
    }

    private function notifyApprover($approval, $contest)
    {
        try {
            if (!$approval->user_id) {
                return;
            }

            $user = User::find($approval->user_id);
            if (!$user || !$user->email) {
                return;
            }

            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/approval-pending.tpl';
            
            $subject = $contest->nombre . ' - Adjudicación pendiente de aprobación';
            
            $html = $this->fetch($template, [
                'title' => 'Adjudicación pendiente de aprobación',
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $contest,
                'approval' => $approval,
                'user' => $user
            ]);
            
            $emailService->send($html, $subject, [$user->email], $user->full_name);

        } catch (\Exception $e) {
            error_log('Error sending approval notification: ' . $e->getMessage());
        }
    }

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
            
            $subject = $contest->nombre . ' - Cadena de aprobacion completada';
            
            $html = $this->fetch($template, [
                'title' => 'Cadena de aprobacion completada',
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $contest,
                'approval' => $approval,
                'user' => $requester
            ]);
            
            $emailService->send($html, $subject, [$requester->email], $requester->full_name);

        } catch (\Exception $e) {
            error_log('Error sending chain complete notification: ' . $e->getMessage());
        }
    }

    private function notifyRejection($approval)
    {
        try {
            $requester = User::find($approval->requester_user_id);
            if (!$requester || !$requester->email) {
                return;
            }

            $contest = Concurso::find($approval->contest_id);
            
            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/adjudication-rejected.tpl';
            
            $subject = $contest->nombre . ' - Adjudicación rechazada';
            
            $html = $this->fetch($template, [
                'title' => 'Adjudicación rechazada',
                'ano' => Carbon::now()->format('Y'),
                'concurso' => $contest,
                'approval' => $approval,
                'user' => $requester,
                'reason' => $approval->reason
            ]);
            
            $emailService->send($html, $subject, [$requester->email], $requester->full_name);

        } catch (\Exception $e) {
            error_log('Error sending rejection notification: ' . $e->getMessage());
        }
    }
}