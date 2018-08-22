<?php

use Kirby\Cms\PageBlueprint;

/**
 * PageBlueprint
 */
return [
    'fields' => [
        'name' => function (PageBlueprint $blueprint) {
            return $blueprint->name();
        },
        'num' => function (PageBlueprint $blueprint) {
            return $blueprint->num();
        },
        'options' => function (PageBlueprint $blueprint) {
            return $blueprint->options();
        },
        'status' => function (PageBlueprint $blueprint) {
            return $blueprint->status();
        },
        'tabs' => function (PageBlueprint $blueprint) {
            return $blueprint->tabs();
        },
        'title' => function (PageBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => PageBlueprint::class,
    'views' => [
    ],
];
