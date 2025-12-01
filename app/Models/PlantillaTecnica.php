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
        $puntaje_minimo = 0;

        foreach (json_decode($this->getAttribute('atributos')) as $atributo) {
            if (!isset($atributo->ponderacion)) {
                $atributo->ponderacion = '';
            }
            
            // Extraer puntaje_minimo del primer elemento (Puntaje mínimo necesario)
            if (isset($atributo->atributo) && $atributo->atributo === 'Puntaje mínimo necesario') {
                $puntaje_minimo = isset($atributo->puntaje) ? $atributo->puntaje : 0;
            }
            
            $result = $result->push($atributo);
        }

        // Ordenamiento numérico para Plantilla 9 (Items)
        // Obtener el tipo de plantilla desde el concurso relacionado
        $plantilla_tipo = $this->concurso ? $this->concurso->ficha_tecnica_plantilla : null;
        
        if ($plantilla_tipo == 9) {
            $sorted = $result->sortBy(function($item) {
                if (isset($item->atributo) && preg_match('/Item (\d+)/', $item->atributo, $matches)) {
                    return (int)$matches[1];
                }
                return 0;
            })->values();
        } else {
            $sorted = $result->sortBy('atributo')->values();
        }
        
        // Agregar propiedades puntaje_minimo y total al collection
        $sorted->puntaje_minimo = $puntaje_minimo;
        $sorted->total = $sorted->where('atributo', '!=', 'Puntaje mínimo necesario')->sum('ponderacion');
        
        return $sorted;
    }
}