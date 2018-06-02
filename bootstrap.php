<?php

if (is_dir(__DIR__ . '/vendor')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    require __DIR__ . '/../../autoload.php';
}

define('DS', '/');
