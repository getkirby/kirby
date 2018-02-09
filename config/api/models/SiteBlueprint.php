<?php

use Kirby\Cms\SiteBlueprint;

/**
 * SiteBlueprint
 */
return [
    'fields' => [
        'name' => function (SiteBlueprint $blueprint) {
            return $blueprint->name();
        },
        'tabs' => function (SiteBlueprint $blueprint) {
            return $blueprint->tabs()->toArray();
        },
        'title' => function (SiteBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => SiteBlueprint::class,
    'views' => [
    ],
];
