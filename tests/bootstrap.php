<?php

use Kirby\PhpUnitExtension;

// enable all error handling as early as possible to
// make debugging of issues in the test setup easier
error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
setlocale(LC_ALL, 'C');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/PhpUnitExtension.php';

// ensure that all mocks are loaded before tests start running
// to avoid issues when running via ParaTest
require_once __DIR__ . '/Api/mocks.php';
require_once __DIR__ . '/Auth/mocks.php';
require_once __DIR__ . '/Cache/mocks.php';
require_once __DIR__ . '/Cms/mocks.php';
require_once __DIR__ . '/Cms/System/mocks.php';
require_once __DIR__ . '/Content/mocks.php';
require_once __DIR__ . '/Data/mocks.php';
require_once __DIR__ . '/Database/mocks.php';
require_once __DIR__ . '/Filesystem/mocks.php';
require_once __DIR__ . '/Http/mocks.php';
require_once __DIR__ . '/Sane/mocks.php';
require_once __DIR__ . '/Session/mocks.php';
require_once __DIR__ . '/Toolkit/mocks.php';

PhpUnitExtension::init();
