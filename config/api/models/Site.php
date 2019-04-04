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
        'drafts' => function (Site $site) {
            return $site->drafts();
        },
        'files' => function (Site $site) {
            return $site->files()->sortBy('sort', 'asc');
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
            'content',
            'options',
            'url'
        ],
        'selector' => [
            'title',
            'children' => [
                'id',
                'title',
                'panelIcon',
                'hasChildren'
            ],
        ]
    ]
];
