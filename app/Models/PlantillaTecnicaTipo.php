<?php

namespace App\Models;

use App\Models\Model;

class PlantillaTecnicaTipo extends Model
{
    protected $table = 'plantilla_precalificacion_tecnica';

    public static function getList() 
    {
        $result = [];

        foreach (self::all() as $plantilla) {
            $result[] = [
                'id'    => (string) $plantilla->id,
                'text'  => $plantilla->nombre
            ];                
        }
        
        return $result;
    }
}