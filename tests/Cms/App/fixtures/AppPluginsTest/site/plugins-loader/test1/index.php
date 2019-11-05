<?php

use Kirby\Cms\AppPluginsTest;

Kirby::plugin('kirby/test1', [
    'hooks' => [
        'system.loadPlugins:after' => function () {
            AppPluginsTest::$calledPluginsLoadedHook = true;
        }
    ]
]);
