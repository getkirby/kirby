<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'pages/(:all)/files/(:any)/meta',
    'action'  => function ($path, $filename) {

        if ($page = $this->site()->find($path)) {

            $root      = $this->app()->root('blueprints');
            $blueprint = new Blueprint($root, $page->template());
            $schema    = new Schema($page, $blueprint->toArray(), $this->app()->schema());

            return $schema->meta();

        }

    }
];
