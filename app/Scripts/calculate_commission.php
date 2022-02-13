<?php
/**
 * Run with: php artisan script app/Scripts/calculate_commission.php
 */

use App\Application;

if (!$args || empty($args)) {
    echo 'Please provide csv input file absolute path.' . PHP_EOL;

    exit('Exiting...' . PHP_EOL);
}

$application = resolve(Application::class);
$application->setInputFilePath($args[0]);
$application->run();
