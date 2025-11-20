<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Concurso;
use App\Models\PlantillaTecnicaItem;

class PlantillaTecnica extends Model
{
    protected $table = 'plantilla_precalificacion_tecnica_concurso';

    protected $fillable = [
    	'id',
    	'atributos'
    ];

    protected $appends = [
        'parsed_items'
    ];

    public function concurso()
    {
    	return $this->belongsTo(Concurso::class, 'id_concurso', 'id');
    }

    public function items()
    {
        return $this->hasMany(PlantillaTecnicaItem::class, 'id_plantilla', 'id');
    }

    public function getParsedItemsAttribute()
    {
        $result = collect();

        foreach (json_decode($this->getAttribute('atributos')) as $atributo) {
            if (!isset($atributo->ponderacion)) {
                $atributo->ponderacion = '';
            }
            $result = $result->push($atributo);
        }

        return $result;
    }
}