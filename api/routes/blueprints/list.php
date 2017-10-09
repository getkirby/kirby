<?php

return [
    'pattern' => 'blueprints',
    'action'  => function () {
        $blueprints = (require __DIR__ . '/../../data/blueprints.php')($this->app()->root('blueprints'));
        return array_values($blueprints);
    }
];
