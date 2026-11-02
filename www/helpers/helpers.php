<?php

use PHPFramework\Application;
use PHPFramework\Request;

function app(): Application
{
    return Application::$app;
}

function request(): Request
{
    return app()->request;
}