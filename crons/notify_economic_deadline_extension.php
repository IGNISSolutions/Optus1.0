<?php

require_once __DIR__ . '/../autoload.php';

use App\Commands\NotifyEconomicDeadlineExtensionTask;

$task = new NotifyEconomicDeadlineExtensionTask();
$result = $task->execute($argv);

exit(!empty($result['success']) ? 0 : 1);
