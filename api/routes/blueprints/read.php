<?php

return [
    'pattern' => 'blueprints/(:any)',
    'action'  => function ($name) {

        $blueprintFile   = $this->app()->root('blueprints') . '/' . $name . '.yml';
        $blueprintReader = require __DIR__ . '/../../data/blueprint.php';

        if (file_exists($blueprintFile) === false) {
            $blueprintFile = $this->app()->root('blueprints') . '/default.yml';
        }

        return $blueprintReader($blueprintFile);
    }
];
