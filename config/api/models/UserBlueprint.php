<?php

use Kirby\Cms\UserBlueprint;

/**
 * UserBlueprint
 */
return [
    'fields' => [
        'name' => function (UserBlueprint $blueprint) {
            return $blueprint->name();
        },
        'options' => function (UserBlueprint $blueprint) {
            return $blueprint->options()->toArray();
        },
        'tabs' => function (UserBlueprint $blueprint) {
            return $blueprint->tabs()->toArray();
        },
        'title' => function (UserBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => UserBlueprint::class,
    'views' => [
    ],
];
