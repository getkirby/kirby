<?php

use Kirby\PhpUnitExtension;

// enable all error handling as early as possible to
// make debugging of issues in the test setup easier
error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/PhpUnitExtension.php';

PhpUnitExtension::init();
