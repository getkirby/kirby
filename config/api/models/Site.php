<?php

use Kirby\Cms\Form;
use Kirby\Cms\Site;

/**
 * Site
 */
return [
    'default' => function () {
        return $this->site();
    },
    'fields' => [
        'blueprint' => function (Site $site) {
            return $site->blueprint();
        },
        'children' => function (Site $site) {
            return $site->children();
        },
        'content' => function (Site $site) {
            return Form::for($site)->values();
        },
        'files' => function (Site $site) {
            return $site->files();
        },
        'options' => function (Site $site) {
            return $site->permissions()->toArray();
        },
        'title' => function (Site $site) {
            return $site->title()->value();
        },
        'url' => function (Site $site) {
            return $site->url();
        },
    ],
    'type' => Site::class,
    'views' => [
        'compact' => [
            'title',
            'url'
        ],
        'default' => [
            'content',
            'options',
            'title',
            'url'
        ],
        'panel' => [
            'title',
            'blueprint',
            'options',
            'url'
        ]
    ]
];
