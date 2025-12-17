<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Agregar columna para guardar fecha/hora de apertura de sobres
 * 
 * Esta columna almacenará la fecha y hora en que el cliente presionó
 * el botón "Ver Ofertas" para abrir los sobres económicos.
 * 
 * Se mostrará en el reporte de trazabilidad (descarga-informe.php) 
 * en la sección "5. Adjudicación"
 */

if (!Capsule::schema()->hasColumn('concursos', 'fecha_apertura_sobres')) {
    Capsule::schema()->table('concursos', function ($table) {
        $table->timestamp('fecha_apertura_sobres')->nullable()->after('aperturasobre');
    });
    echo "✅ Columna 'fecha_apertura_sobres' agregada a tabla 'concursos'\n";
} else {
    echo "⚠️ La columna 'fecha_apertura_sobres' ya existe en tabla 'concursos'\n";
}
