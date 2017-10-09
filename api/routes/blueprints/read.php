<?php

return [
    'pattern' => 'blueprints/(:any)',
    'action'  => function ($name) {
        $blueprints = (require __DIR__ . '/../../data/blueprints.php')($this->app()->root('blueprints'));
        return $blueprints[$name];
    }
];
