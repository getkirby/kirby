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
            return $blueprint->options();
        },
        'tabs' => function (UserBlueprint $blueprint) {
            return $blueprint->tabs();
        },
        'title' => function (UserBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => 'Kirby\Cms\UserBlueprint',
    'views' => [
    ],
];
