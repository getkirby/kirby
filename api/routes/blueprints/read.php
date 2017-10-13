<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'blueprints/(:any)',
    'action'  => function ($name) {

        $blueprint = new Blueprint($this->app()->root('blueprints'), $name);
        $schema    = new Schema(null, $blueprint->toArray(), $this->app()->schema());

        return $schema->toArray();

    }
];
