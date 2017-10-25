<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'site/blueprints',
    'action'  => function () {

        $root      = $this->app()->root('blueprints');
        $blueprint = new Blueprint($root, 'site');
        $schema    = new Schema($this->site(), $blueprint->toArray(), $this->app()->schema());
        $result    = [];

        foreach ($schema->blueprints() as $blueprintName) {
            $blueprint = new Blueprint($root, $blueprintName);
            $result[] = $blueprint->toArray();
        }

        return $result;

    }
];
