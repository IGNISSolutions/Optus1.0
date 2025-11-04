<?php

/**
 * Cron Job: Verificar si todos los proveedores presentaron propuestas económicas
 * 
 * Descripción:
 * Este cron verifica concursos donde TODOS los participantes que estaban en 
 * "economica-pendiente" pasaron a "economica-presentada" ANTES de la fecha límite.
 * Envía un email al cliente notificándole que puede proceder con la evaluación.
 * 
 * Configuración:
 * - Frecuencia recomendada: Cada 5 minutos
 * - Windows Task Scheduler: *5 * * * * php C:\xampp\htdocs\crons\economic_stage_all_submitted.php
 * - Linux Crontab: *5 * * * * php /path/to/crons/economic_stage_all_submitted.php
 */

// Cargar autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar bootstrap de la aplicación
require_once __DIR__ . '/../bootstrap/app.php';

echo "===========================================\n";
echo "Iniciando tarea: Propuestas Completadas\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    // Crear instancia de la tarea y ejecutar
    $task = new \App\Commands\EconomicStageAllSubmittedTask();
    $result = $task->execute();
    
    if ($result['success']) {
        echo "\n✅ Tarea completada exitosamente\n";
        exit(0);
    } else {
        echo "\n❌ Tarea completada con errores\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "\n❌ ERROR FATAL: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
