<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;

/**
 * Api Model Definitions
 */
return [
    'File'          => include __DIR__ . '/models/File.php',
    'FileBlueprint' => include __DIR__ . '/models/FileBlueprint.php',
    'FileVersion'   => include __DIR__ . '/models/FileVersion.php',
    'Language'      => include __DIR__ . '/models/Language.php',
    'Page'          => include __DIR__ . '/models/Page.php',
    'PageBlueprint' => include __DIR__ . '/models/PageBlueprint.php',
    'Role'          => include __DIR__ . '/models/Role.php',
    'Site'          => include __DIR__ . '/models/Site.php',
    'SiteBlueprint' => include __DIR__ . '/models/SiteBlueprint.php',
    'System'        => include __DIR__ . '/models/System.php',
    'Translation'   => include __DIR__ . '/models/Translation.php',
    'User'          => include __DIR__ . '/models/User.php',
    'UserBlueprint' => include __DIR__ . '/models/UserBlueprint.php',
];
