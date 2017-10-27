<?php

use Kirby\Cms\PageBlueprint;

return [
    'pattern' => 'pages/(:all)/blueprints',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {

            $blueprint = new PageBlueprint($page->template());
            $result    = [];

            foreach ($blueprint->blueprints() as $blueprintName) {
                $pageBlueprint = new PageBlueprint($blueprintName);
                $result[]      = $pageBlueprint->toArray();
            }

            return $result;

        }

    }
];
