<?php

namespace App\Models;

use App\Models\Model;

class PlantillaTecnicaTipo extends Model
{
    protected $table = 'plantilla_precalificacion_tecnica';

    public static function getList() 
    {
        $result = [];
        $user = user();
        $userCustomerCompanyId = $user->customer_company_id ?? null;

        // Obtener plantillas ordenadas: primero las generales (1 y 9), luego el resto alfabéticamente
        $plantillas = self::orderByRaw('CASE WHEN id IN (1, 9) THEN 0 ELSE 1 END, nombre ASC')->get();

        foreach ($plantillas as $plantilla) {
            // La plantilla con id = 1 o 9 es general y debe aparecer para todos
            // Las demás plantillas solo aparecen si el customer_company_id del usuario coincide
            if ($plantilla->id == 1 || $plantilla->id == 9 || $plantilla->customer_company_id == $userCustomerCompanyId) {
                $result[] = [
                    'id'    => (string) $plantilla->id,
                    'text'  => $plantilla->nombre
                ];
            }
        }
        
        return $result;
    }
}