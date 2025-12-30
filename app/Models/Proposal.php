<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\Participante;
use App\Models\ProposalType;
use App\Models\ProposalStatus;
use App\Models\ProposalDocument;
use App\Models\ProposalDocumentType;
use Carbon\Carbon;

class Proposal extends Model
{
    use SoftDeletes;
    
    protected $table = 'proposals';

    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'id',
        'participante_id',
        'type_id',
        'comment',
        'values',
        'status_id',
        'payment_deadline',
        'numero_ronda',
        'payment_condition',
        'ronda_tecnica',
        'comentario_nueva_ronda'
    ];

    protected $appends = [
        'is_technical',
        'is_economic',
        'is_accepted',
        'is_rejected',
        'is_pending',
        'is_expired',
        'is_revisada'
    ];

    public function oferente()
    {
    	return $this->belongsTo(Participante::class, 'participante_id', 'id');
    }

    public function type()
    {
    	return $this->belongsTo(ProposalType::class, 'type_id', 'id');
    }

    public function status()
    {
    	return $this->hasOne(ProposalStatus::class, 'id', 'status_id');
    }

    public function documents()
    {
        return $this->hasMany(ProposalDocument::class, 'proposal_id', 'id');
    }

    public function getValuesAttribute()
    {
        $values = 
            $this->attributes['values'] ? 
            json_decode($this->attributes['values'], true) : 
            null;

        if ($values) {
            foreach ($values as &$value) {
                // Convertir cotización y cantidad a float para asegurar formato numérico correcto
                $value['cotizacion'] = isset($value['cotizacion']) ? (float) $value['cotizacion'] : null;
                $value['cantidad'] = isset($value['cantidad']) ? (float) $value['cantidad'] : null;
                $value['cotUnitaria'] = $value['cotizacion'];
                $value['cantidadCot'] = $value['cantidad'];
                $value['total'] = (float) $value['cotUnitaria'] * (float) $value['cantidadCot'];
            }
        }

        return $values;
    }

    public function getIsTechnicalAttribute()
    {
        return $this->type->code === ProposalType::CODES['technical'];
    }

    public function getIsEconomicAttribute()
    {
        return $this->type->code === ProposalType::CODES['economic'];
    }

    public static function getTechnicalDocumentsTypes()
    {
        return ProposalDocumentType::all()->filter(function ($item) {
            return $item->is_technical;
        });
    }

    public static function getEconomicDocumentsTypes()
    {
        return ProposalDocumentType::all()->filter(function ($item) {
            return $item->is_economic;
        });
    }

    public function getIsAcceptedAttribute()
    {
        return $this->status->is_accepted;
    }

    public function getIsPendingAttribute()
    {
        return $this->status->is_pending;
    }

    public function getIsRejectedAttribute()
    {
        return $this->status->is_rejected;
    }

    public function getIsExpiredAttribute()
    {
        return $this->status->is_expired;
    }
    public function getIsRevisadaAttribute()
    {
        return $this->status->is_revisada;
    }
}