<?php

if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    // always prefer a site-wide Composer autoloader
    // if it exists, it means that the user has probably installed additional packages
    require dirname(__DIR__) . '/vendor/autoload.php';
} elseif (is_file(__DIR__ . '/vendor/autoload.php')) {
    // fall back to the local autoloader if that exists
    require __DIR__ . '/vendor/autoload.php';
} else {
    // if neither one exists, don't bother searching
    // it's a custom directory setup and the users need to load the autoloader themselves
}

define('DS', '/');
