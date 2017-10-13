<?php

use Kirby\Cms\Blueprint;

return [
    'pattern' => 'blueprints/(:any)',
    'action'  => function ($name) {
        $blueprint = new Blueprint($this->app()->root('blueprints'), $name);
        return $blueprint->toArray();
    }
];
