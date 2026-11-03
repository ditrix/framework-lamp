<?php

/** @var PHPFramework\Application $app */

use App\Controllers\HomeController;
use App\Controllers\ContactController;

$app->router->add('/', function () {
    //return 'Hello World';
}, ['post', 'get']);

$app->router->get('/test', [HomeController::class, 'test']);

$app->router->post('/contact', [ContactController::class, 'contact']);

dump($app->router->getRoutes());
