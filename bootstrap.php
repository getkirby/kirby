<?php

if (is_dir(__DIR__ . '/vendor')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    require __DIR__ . '/../../autoload.php';
}

require __DIR__ . '/config/sentry.php';
require __DIR__ . '/extensions/helpers.php';
require __DIR__ . '/config/aliases.php';
