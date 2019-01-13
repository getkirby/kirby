<?php

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
