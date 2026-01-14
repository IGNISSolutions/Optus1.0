<?php

namespace App\Models;

use App\Models\Model;

class EstrategiaLiberacion extends Model
{
  
    protected $table = 'estrategia_liberacion';

    public $timestamps = true;

    protected $fillable = [
        'customer_company_id',
        'monto_nivel_0',
        'monto_nivel_1',
        'jefe_compras',
        'jefe_solicitante',
        'monto_nivel_2',
        'gerente_compras',
        'gerente_solicitante',
        'monto_nivel_3',
        'gerente_general',
        'habilitado'
    ];

    protected $casts = [
        'jefe_compras' => 'boolean',
        'jefe_solicitante' => 'boolean',
        'gerente_compras' => 'boolean',
        'gerente_solicitante' => 'boolean',
        'gerente_general' => 'boolean',
        'habilitado' => 'boolean',
        'monto_nivel_0' => 'decimal:2',
        'monto_nivel_1' => 'decimal:2',
        'monto_nivel_2' => 'decimal:2',
        'monto_nivel_3' => 'decimal:2'
    ];

    /**
     * Obtener la estrategia de una empresa
     */
    public static function getByCompany($customerCompanyId) 
    {
        return self::where('customer_company_id', $customerCompanyId)->first();
    }

    /**
     * Guardar o actualizar la estrategia de una empresa
     */
    public static function saveStrategy($customerCompanyId, $data)
    {
        $estrategia = self::getByCompany($customerCompanyId);
        
        if (!$estrategia) {
            $estrategia = new self();
            $estrategia->customer_company_id = $customerCompanyId;
        }
        
        $estrategia->monto_nivel_0 = $data['monto_nivel_0'] ?? 0;
        $estrategia->monto_nivel_1 = $data['monto_nivel_1'] ?? 0;
        $estrategia->jefe_compras = $data['jefe_compras'] ?? false;
        $estrategia->jefe_solicitante = $data['jefe_solicitante'] ?? false;
        $estrategia->monto_nivel_2 = $data['monto_nivel_2'] ?? 0;
        $estrategia->gerente_compras = $data['gerente_compras'] ?? false;
        $estrategia->gerente_solicitante = $data['gerente_solicitante'] ?? false;
        $estrategia->monto_nivel_3 = $data['monto_nivel_3'] ?? 0;
        $estrategia->gerente_general = $data['gerente_general'] ?? false;
        $estrategia->habilitado = $data['habilitado'] ?? false;
        
        $estrategia->save();
        
        return $estrategia;
    }

    public static function getList() 
    {
        $result = [];
        
        foreach (self::all() as $estrategia) {
            $result[] = [
                'id' => (int) $estrategia->id,
                'customer_company_id' => (int) $estrategia->customer_company_id,
                'monto_nivel_0' => (float) $estrategia->monto_nivel_0,
                'monto_nivel_1' => (float) $estrategia->monto_nivel_1,
                'jefe_compras' => (bool) $estrategia->jefe_compras,
                'jefe_solicitante' => (bool) $estrategia->jefe_solicitante,
                'monto_nivel_2' => (float) $estrategia->monto_nivel_2,
                'gerente_compras' => (bool) $estrategia->gerente_compras,
                'gerente_solicitante' => (bool) $estrategia->gerente_solicitante,
                'monto_nivel_3' => (float) $estrategia->monto_nivel_3,
                'gerente_general' => (bool) $estrategia->gerente_general,
                'habilitado' => (bool) $estrategia->habilitado
            ];
        }

        return $result;
    }
}