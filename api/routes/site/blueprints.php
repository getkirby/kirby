<?php

use Kirby\Cms\PageBlueprint;
use Kirby\Cms\SiteBlueprint;

return [
    'auth'    => true,
    'pattern' => 'site/blueprints',
    'action'  => function () {

        $blueprint = new SiteBlueprint();
        $result    = [];

        foreach ($blueprint->blueprints() as $blueprintName) {
            $pageBlueprint = new PageBlueprint($blueprintName);
            $result[]      = $pageBlueprint->toArray();
        }

        return $result;

    }
];
