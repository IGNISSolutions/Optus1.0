<?php

namespace App\Models;

use App\Models\Model;
use Carbon\Carbon;

class AdjudicationApproval extends Model
{
    protected $table = 'adjudication_approvals';
    public $timestamps = true;

    protected $fillable = [
        'contest_id', 'batch_id', 'adjudication_type', 'amount', 'amount_usd',
        'adjudication_data', 'adjudication_comment', 'level', 'role',
        'area', 'threshold_amount', 'user_id', 'user_name', 'status',
        'sort_order', 'response_date', 'reason', 'approved_by_user_id',
        'requester_user_id'
    ];

    protected $casts = [
        'contest_id' => 'integer',
        'batch_id' => 'integer',
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

    /**
     * Crear solicitud de aprobación - SIMPLIFICADO
     * Los user_id ya vienen del controlador
     */
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

        // Obtener el siguiente batch_id para este concurso
        $lastBatch = self::where('contest_id', $contestId)->max('batch_id') ?? 0;
        $newBatchId = $lastBatch + 1;

        foreach ($approvalLevels as $level) {
            $record = self::create([
                'contest_id' => $contestId,
                'batch_id' => $newBatchId,
                'adjudication_type' => $adjudicationType,
                'amount' => $amount,
                'amount_usd' => $amountUsd,
                'adjudication_data' => $adjudicationData,
                'adjudication_comment' => $comment,
                'level' => $level['level'],
                'role' => $level['role'],
                'area' => $level['area'] ?? null,
                'threshold_amount' => $level['threshold_amount'] ?? 0,
                'user_id' => $level['user_id'] ?? null,
                'user_name' => $level['user_name'] ?? null,
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

    /**
     * Obtener el batch_id actual (más reciente) para un concurso
     */
    public static function getCurrentBatchId($contestId)
    {
        return self::where('contest_id', $contestId)->max('batch_id') ?? 0;
    }

    /**
     * Obtener todos los registros del batch actual (cadena activa)
     */
    public static function getByContest($contestId)
    {
        $currentBatch = self::getCurrentBatchId($contestId);
        
        return self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Obtener historial de cadenas rechazadas (batches anteriores rechazados)
     */
    public static function getRejectedHistory($contestId)
    {
        $currentBatch = self::getCurrentBatchId($contestId);
        
        // Obtener los batch_ids que fueron rechazados (excluyendo el actual)
        $rejectedBatches = self::where('contest_id', $contestId)
            ->where('batch_id', '<', $currentBatch)
            ->where('status', self::STATUS_REJECTED)
            ->distinct()
            ->pluck('batch_id');
        
        if ($rejectedBatches->isEmpty()) {
            return collect([]);
        }
        
        // Obtener los registros de cada batch rechazado
        return self::where('contest_id', $contestId)
            ->whereIn('batch_id', $rejectedBatches)
            ->orderBy('batch_id', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get()
            ->groupBy('batch_id');
    }

    /**
     * Verificar si el usuario puede aprobar - SIMPLIFICADO
     * Solo verifica si el user_id coincide con el nivel pendiente del batch actual
     */
    public static function canApprove($contestId, $userId)
    {
        $currentBatch = self::getCurrentBatchId($contestId);
        
        $pending = self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->where('status', self::STATUS_PENDING)
            ->orderBy('sort_order', 'asc')
            ->first();

        if (!$pending) {
            return null;
        }

        if ($pending->user_id && $pending->user_id == $userId) {
            return $pending;
        }
        
        return null;
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
        $currentBatch = self::getCurrentBatchId($this->contest_id);
        
        $allApproved = self::where('contest_id', $this->contest_id)
            ->where('batch_id', $currentBatch)
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
        $currentBatch = self::getCurrentBatchId($contestId);
        
        $total = self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->count();
        $approved = self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->where('status', self::STATUS_APPROVED)
            ->count();

        return $total > 0 && $total === $approved;
    }

    public static function isChainRejected($contestId)
    {
        $currentBatch = self::getCurrentBatchId($contestId);
        
        return self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->where('status', self::STATUS_REJECTED)
            ->exists();
    }

    public static function getNextPending($contestId)
    {
        $currentBatch = self::getCurrentBatchId($contestId);
        
        return self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->where('status', self::STATUS_PENDING)
            ->orderBy('sort_order', 'asc')
            ->first();
    }

    public static function cancelApprovals($contestId)
    {
        // Solo elimina el batch actual, conserva historial
        $currentBatch = self::getCurrentBatchId($contestId);
        
        self::where('contest_id', $contestId)
            ->where('batch_id', $currentBatch)
            ->delete();

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
            'batch_id' => $this->batch_id,
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
