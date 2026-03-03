<?php

use Illuminate\Database\Capsule\Manager as DB;

// Agregar campo batch_id para agrupar cadenas de aprobación
// Esto permite conservar el historial cuando se rechaza y se crea una nueva cadena

$tableName = 'adjudication_approvals';

if (!DB::schema()->hasColumn($tableName, 'batch_id')) {
    DB::schema()->table($tableName, function ($table) {
        // batch_id agrupa los niveles de una misma cadena de aprobación
        // Cuando se rechaza y se crea una nueva, se usa un nuevo batch_id
        $table->integer('batch_id')->default(1)->after('contest_id');
        
        // Índice para búsquedas por contest_id y batch_id
        $table->index(['contest_id', 'batch_id'], 'idx_contest_batch');
    });
    
    echo "Campo batch_id agregado a $tableName\n";
}

echo "Migración 8_2_add_batch_id_adjudication_approvals completada.\n";
