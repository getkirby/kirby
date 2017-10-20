<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'pages/(:all)/blueprints',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {

            $root      = $this->app()->root('blueprints');
            $blueprint = new Blueprint($root, $page->template());
            $schema    = new Schema($page, $blueprint->toArray(), $this->app()->schema());
            $result    = [];

            foreach ($schema->blueprints() as $blueprintName) {
                $blueprint = new Blueprint($root, $blueprintName);
                $result[] = $blueprint->toArray();
            }

            return $result;

        }

    }
];
