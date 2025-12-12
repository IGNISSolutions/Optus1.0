<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Agregar columna para registrar si el tiempo adicional ya fue aplicado
 * 
 * Esta columna evita que el tiempo adicional de las subastas se sume 
 * múltiples veces (una por cada oferente) en lugar de aplicarse una sola vez.
 */

if (!Capsule::schema()->hasColumn('concursos', 'tiempo_adicional_aplicado')) {
    Capsule::schema()->table('concursos', function ($table) {
        $table->boolean('tiempo_adicional_aplicado')->default(false)->after('tiempo_adicional');
    });
    echo "✅ Columna 'tiempo_adicional_aplicado' agregada a tabla 'concursos'\n";
    
    // Marcar como aplicado para subastas que ya finalizaron (para evitar problemas con concursos antiguos)
    Capsule::table('concursos')
        ->where('is_online', true)
        ->where('inicio_subasta', '<', date('Y-m-d H:i:s'))
        ->whereRaw('DATE_ADD(inicio_subasta, INTERVAL duracion SECOND) < NOW()')
        ->update(['tiempo_adicional_aplicado' => true]);
    
    echo "✅ Subastas finalizadas marcadas con tiempo_adicional_aplicado = true\n";
} else {
    echo "⚠️ La columna 'tiempo_adicional_aplicado' ya existe en tabla 'concursos'\n";
}
