<?php

use Kirby\Cms\System;

return [
    'pattern' => 'panel/system',
    'action'  => function () {

        $system = $this->app()->system();

        return [
            'isOk'        => $system->isOk(),
            'isInstalled' => $system->isInstalled(),
            'details'     => $system->status(),
        ];

    }
];
