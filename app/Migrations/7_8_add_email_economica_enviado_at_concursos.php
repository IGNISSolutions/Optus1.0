<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Agregar columna para controlar envío de email de finalización de etapa económica
 * 
 * Esta columna almacenará la fecha y hora en que se envió el email de notificación
 * cuando finaliza la etapa económica de un concurso.
 * 
 * IMPORTANTE: Los concursos ya finalizados se marcarán automáticamente como "email enviado"
 * para evitar enviar notificaciones de eventos pasados.
 */

Capsule::schema()->table('concursos', function ($table) {
    // Agregar columna para registrar cuándo se envió el email de finalización
    $table->timestamp('email_economica_enviado_at')->nullable()->after('fecha_limite_economicas');
});

echo "✅ Columna 'email_economica_enviado_at' agregada a tabla 'concursos'\n";

// Marcar como "ya enviado" todos los concursos cuya fecha límite económica ya pasó
// Esto evita que se envíen emails de concursos finalizados en el pasado
$updated = Capsule::table('concursos')
    ->whereNotNull('fecha_limite_economicas')
    ->where('fecha_limite_economicas', '<', Capsule::raw('NOW()'))
    ->whereNull('email_economica_enviado_at')
    ->update(['email_economica_enviado_at' => Capsule::raw('NOW()')]);

echo "✅ Marcados {$updated} concursos finalizados como 'email enviado' (sin enviar emails reales)\n";
echo "✅ Migración completada exitosamente\n";
