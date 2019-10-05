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
        'options' => function (SiteBlueprint $blueprint) {
            return $blueprint->options();
        },
        'tabs' => function (SiteBlueprint $blueprint) {
            return $blueprint->tabs();
        },
        'title' => function (SiteBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => 'Kirby\Cms\SiteBlueprint',
    'views' => [
    ],
];
