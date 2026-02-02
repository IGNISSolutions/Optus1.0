<?php

namespace App\Models;

use App\Models\Model;
use Carbon\Carbon;

class AdjudicationApproval extends Model
{
    protected $table = 'adjudication_approvals';
    public $timestamps = true;

    protected $fillable = [
        'contest_id', 'adjudication_type', 'amount', 'amount_usd',
        'adjudication_data', 'adjudication_comment', 'level', 'role',
        'area', 'threshold_amount', 'user_id', 'user_name', 'status',
        'sort_order', 'response_date', 'reason', 'approved_by_user_id',
        'requester_user_id'
    ];

    protected $casts = [
        'contest_id' => 'integer',
        'amount' => 'decimal:2',
        'amount_usd' => 'decimal:2',
        'threshold_amount' => 'decimal:2',
        'adjudication_data' => 'array',
        'level' => 'integer',
        'user_id' => 'integer',
        'sort_order' => 'integer',
        'requester_user_id' => 'integer',
        'approved_by_user_id' => 'integer',
        'response_date' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function contest()
    {
        return $this->belongsTo(Concurso::class, 'contest_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public static function createApprovalRequest(
        $contestId,
        $adjudicationType,
        $amount,
        $amountUsd,
        $approvalLevels,
        $adjudicationData,
        $comment,
        $requesterUserId
    ) {
        $records = [];

        foreach ($approvalLevels as $level) {
            $userId = self::findApproverUserId($level['role'], $level['user'] ?? null, $contestId);

            $record = self::create([
                'contest_id' => $contestId,
                'adjudication_type' => $adjudicationType,
                'amount' => $amount,
                'amount_usd' => $amountUsd,
                'adjudication_data' => $adjudicationData,
                'adjudication_comment' => $comment,
                'level' => $level['level'],
                'role' => $level['role'],
                'area' => $level['area'] ?? null,
                'threshold_amount' => $level['threshold_amount'] ?? 0,
                'user_id' => $userId,
                'user_name' => $level['user'],
                'status' => self::STATUS_PENDING,
                'sort_order' => $level['sort_order'],
                'requester_user_id' => $requesterUserId
            ]);

            $records[] = $record;
        }

        Concurso::where('id', $contestId)->update([
            'adjudication_pending_approval' => 1,
            'adjudication_rejected' => 0
        ]);

        return $records;
    }

    private static function findApproverUserId($role, $userName, $contestId)
    {
        if (empty($userName)) {
            return null;
        }

        $contest = Concurso::find($contestId);
        if (!$contest) {
            return null;
        }

        // Concurso uses id_cliente which references customer_company
        $customerCompanyId = $contest->id_cliente;

        $nameParts = explode(' ', trim($userName), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $query = User::where('customer_company_id', $customerCompanyId)
            ->whereNull('deleted_at');

        if ($firstName && $lastName) {
            $user = $query->where('first_name', $firstName)
                         ->where('last_name', $lastName)
                         ->first();
            if ($user) {
                return $user->id;
            }
        }

        // Fallback: try matching by full name in either order
        if ($userName) {
            $user = User::where('customer_company_id', $customerCompanyId)
                ->whereNull('deleted_at')
                ->whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$userName])
                ->first();
            if ($user) {
                return $user->id;
            }
        }

        return null;
    }

    public static function getByContest($contestId)
    {
        return self::where('contest_id', $contestId)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public static function canApprove($contestId, $userId)
    {
        $approvals = self::where('contest_id', $contestId)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($approvals->isEmpty()) {
            return null;
        }

        $pending = $approvals->where('status', self::STATUS_PENDING)->first();
        if (!$pending) {
            return null;
        }

        // Direct user match
        if ($pending->user_id && $pending->user_id == $userId) {
            return $pending;
        }

        // Role-based match when user_id is null
        if (!$pending->user_id) {
            $user = User::find($userId);
            if ($user && self::userMatchesRole($user, $pending->role, $pending->area)) {
                return $pending;
            }
        }

        return null;
    }

    private static function userMatchesRole($user, $requiredRole, $requiredArea)
    {
        if (!$user->rol) {
            return false;
        }

        // Build expected role string: "Gerente de Compras" from user with rol="Gerente", area="Compras"
        $userRoleString = $user->rol;
        if ($user->area) {
            $userRoleString .= ' de ' . $user->area;
        }

        // Exact match
        if ($requiredRole === $userRoleString) {
            return true;
        }

        // Match for roles without area (like "Gerente General")
        if ($requiredRole === $user->rol && empty($requiredArea)) {
            return true;
        }

        return false;
    }

    public function approve($userId, $reason = null)
    {
        $this->status = self::STATUS_APPROVED;
        $this->response_date = Carbon::now();
        $this->reason = $reason;
        $this->approved_by_user_id = $userId;
        $this->save();

        $this->checkChainComplete();
        return $this;
    }

    public function reject($userId, $reason)
    {
        $this->status = self::STATUS_REJECTED;
        $this->response_date = Carbon::now();
        $this->reason = $reason;
        $this->approved_by_user_id = $userId;
        $this->save();

        Concurso::where('id', $this->contest_id)->update([
            'adjudication_pending_approval' => 0,
            'adjudication_rejected' => 1
        ]);

        return $this;
    }

    private function checkChainComplete()
    {
        $allApproved = self::where('contest_id', $this->contest_id)
            ->where('status', '!=', self::STATUS_APPROVED)
            ->doesntExist();

        if ($allApproved) {
            Concurso::where('id', $this->contest_id)->update([
                'adjudication_pending_approval' => 0
            ]);
            return true;
        }

        return false;
    }

    public static function isChainComplete($contestId)
    {
        $total = self::where('contest_id', $contestId)->count();
        $approved = self::where('contest_id', $contestId)
            ->where('status', self::STATUS_APPROVED)
            ->count();

        return $total > 0 && $total === $approved;
    }

    public static function isChainRejected($contestId)
    {
        return self::where('contest_id', $contestId)
            ->where('status', self::STATUS_REJECTED)
            ->exists();
    }

    public static function getNextPending($contestId)
    {
        return self::where('contest_id', $contestId)
            ->where('status', self::STATUS_PENDING)
            ->orderBy('sort_order', 'asc')
            ->first();
    }

    public static function cancelApprovals($contestId)
    {
        self::where('contest_id', $contestId)->delete();

        Concurso::where('id', $contestId)->update([
            'adjudication_pending_approval' => 0,
            'adjudication_rejected' => 0
        ]);
    }

    public static function getPendingByUser($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', self::STATUS_PENDING)
            ->with('contest')
            ->get();
    }

    public function toResponseArray()
    {
        return [
            'id' => $this->id,
            'contest_id' => $this->contest_id,
            'adjudication_type' => $this->adjudication_type,
            'amount' => (float) $this->amount,
            'amount_usd' => (float) $this->amount_usd,
            'level' => $this->level,
            'role' => $this->role,
            'area' => $this->area,
            'threshold_amount' => (float) $this->threshold_amount,
            'user' => $this->user_name,
            'user_id' => $this->user_id,
            'status' => ucfirst($this->status),
            'sort_order' => $this->sort_order,
            'date' => $this->response_date ? $this->response_date->format('d/m/Y H:i') : null,
            'reason' => $this->reason,
            'requester_user_id' => $this->requester_user_id
        ];
    }
}