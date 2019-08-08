<?php

/**
 * Constants
 */
define('DS', '/');

/**
 * Load files that can't be autoloaded
 */
require_once __DIR__ . '/helpers.php';

/**
 * Class aliases
 */
$aliases = require_once __DIR__ . '/aliases.php';

spl_autoload_register(function ($class) use ($aliases) {
    $class = strtolower($class);

    if (isset($aliases[$class]) === true) {
        class_alias($aliases[$class], $class);
    }
});

/**
 * Tests
 */
$testDir = dirname(__DIR__) . '/tests';

if (is_dir($testDir) === true) {
    spl_autoload_register(function ($className) use ($testDir) {
        $path = str_replace('Kirby\\', '', $className);
        $path = str_replace('\\', '/', $path);
        $file = $testDir . '/' . $path . '.php';

        if (file_exists($file)) {
            include $file;
        }
    });
}
