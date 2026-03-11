<?php

namespace App\Models;

use App\Models\Model;

class EstrategiaLiberacion extends Model
{
  
    protected $table = 'estrategia_liberacion';

    public $timestamps = true;

    protected $fillable = [
        'idestrategia_liberacion',
        'habilitado',
        'nivel0',
        'nivel1',
        'nivel2',
        'nivel3',
        'nivel4',
        'customer_company_id'
    ];

    public static function getList() 
    {
        $result = [];
        
        foreach (self::all() as $estrategialiberacion) {
            $result[] = [
                'id'=> (string) $estrategialiberacion->idestrategia_liberacion,
                'habilitado'=> (string) $estrategialiberacion->habilitado,
                'nivel0'=> (string) $estrategialiberacion->nivel0,
                'nivel1'=> (string) $estrategialiberacion->nivel1,
                'nivel2'=> (string) $estrategialiberacion->nivel2,
                'nivel3'=> (string) $estrategialiberacion->nivel3,
                'nivel4'=> (string) $estrategialiberacion->nivel4,
                'compania' => (string) $estrategialiberacion->customer_company_id,

            ];
        }

        return $result;
    }
}