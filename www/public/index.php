<?php

use PHPFramework\Application;

$start_framework = microtime(true);

if (PHP_MAJOR_VERSION < 8) {
    die('Require PHP verion >= 8');
}

require_once __DIR__ . '/../config/config.php';
require_once ROOT . '/vendor/autoload.php';
require_once HELPERS . '/helpers.php';

$app = new Application();

require_once CONFIG . '/routes.php';

$app->run();



echo "<hr>";
