<?php

/**
 * Script para ejecutar la tarea de notificación de finalización de etapa económica
 * Este script debe ser ejecutado por un Cron Job cada minuto
 * 
 * Ejemplo de configuración Cron (Linux):
 * * * * * * php /xampp/htdocs/crons/economic_stage_end.php >> /xampp/htdocs/logs/cron_economic.log 2>&1
 * 
 * Ejemplo de configuración Cron (Windows Task Scheduler):
 * php.exe C:\xampp\htdocs\crons\economic_stage_end.php
 */

require_once __DIR__ . '/../autoload.php';

use App\Commands\EconomicStageEndTask;

echo "===========================================\n";
echo "Iniciando tarea: Notificación Etapa Económica\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $task = new EconomicStageEndTask();
    $result = $task->execute();
    
    if ($result['success']) {
        echo "\n✅ Tarea completada exitosamente\n";
        exit(0);
    } else {
        echo "\n❌ Tarea completada con errores\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "\n❌ ERROR FATAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
