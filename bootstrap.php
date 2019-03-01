<?php

/**
 * Validate the PHP version to already
 * stop at older versions
 */
if (version_compare(phpversion(), '7.1.0', '>=') === false) {
    die(include __DIR__ . '/views/php.php');
}

if (is_file($autoloader = dirname(__DIR__) . '/vendor/autoload.php')) {

    /**
     * Always prefer a site-wide Composer autoloader
     * if it exists, it means that the user has probably
     * installed additional packages
     */
    include $autoloader;
} elseif (is_file($autoloader = __DIR__ . '/vendor/autoload.php')) {

    /**
     * Fall back to the local autoloader if that exists
     */
    include $autoloader;
} else {

    /**
     * If neither one exists, don't bother searching
     * it's a custom directory setup and the users need to
     * load the autoloader themselves
     */
}

define('DS', '/');
