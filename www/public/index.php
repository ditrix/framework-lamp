<?php

$start_framework = microtime(true);

if ( PHP_MAJOR_VERSION < 8) {
    die('Require PHP verion >= 8');
}

//var_dump("Time: ". microtime(true) - $start_framework );