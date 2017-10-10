<?php

return [
    'pattern' => 'blueprints/(:any)',
    'action'  => function ($name) {
        return (require __DIR__ . '/../../data/blueprint.php')($this->app()->root('blueprints') . '/' . $name . '.yml');
    }
];
