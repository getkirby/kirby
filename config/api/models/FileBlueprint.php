<?php

use Kirby\Cms\FileBlueprint;

/**
 * FileBlueprint
 */
return [
    'fields' => [
        'name' => function (FileBlueprint $blueprint) {
            return $blueprint->name();
        },
        'options' => function (FileBlueprint $blueprint) {
            return $blueprint->options()->toArray();
        },
        'tabs' => function (FileBlueprint $blueprint) {
            return $blueprint->tabs()->toArray();
        },
        'title' => function (FileBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => FileBlueprint::class,
    'views' => [
    ],
];
