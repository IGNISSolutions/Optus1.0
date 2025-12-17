<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Solped;
use App\Models\User;
use App\Models\Measurement;

class SolpedItems extends Model
{
    protected $table = 'hijos_x_solped';

    protected $fillable = [
        'id',
        'id_solped',
        'id_usuario_creador',
        'nombre',
        'descripcion',
        'cantidad',
        'oferta_minima',
        'unidad',
        'targetcost',
        'eliminado',
    ];

    protected $casts = [
        'targetcost' => 'float',
        'cantidad'=> 'float',
        'oferta_minima'=> 'float'
    ];

    public function solped()
    {
        return $this->belongsTo(Solped::class, 'id_solped', 'id');
    }

    public function unidad_medida()
    {
        return $this->belongsTo(Measurement::class, 'unidad', 'id');
    }
}