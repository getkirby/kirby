<?php

use Kirby\Cms\Form;
use Kirby\Cms\Page;

/**
 * Page
 */
return [
    'fields' => [
        'blueprint' => function (Page $page) {
            return $page->blueprint();
        },
        'blueprints' => function (Page $page) {
            return $page->blueprints();
        },
        'children' => function (Page $page) {
            return $page->children();
        },
        'content' => function (Page $page) {
            return Form::for($page)->values();
        },
        'errors' => function (Page $page) {
            return $page->errors();
        },
        'files' => function (Page $page) {
            return $page->files();
        },
        'hasChildren' => function (Page $page) {
            return $page->hasChildren();
        },
        'id' => function (Page $page) {
            return $page->id();
        },
        'isSortable' => function (Page $page) {
            return $page->isSortable();
        },
        'next' => function (Page $page) {
            return $page->next();
        },
        'num' => function (Page $page) {
            return $page->num();
        },
        'options' => function (Page $page) {
            return $page->permissions()->toArray();
        },
        'parent' => function (Page $page) {
            return $page->parent();
        },
        'parents' => function (Page $page) {
            return $page->parents()->flip();
        },
        'prev' => function (Page $page) {
            return $page->prev();
        },
        'siblings' => function (Page $page) {
            if ($page->isDraft() === true) {
                return $page->parentModel()->children()->not($page);
            } else {
                return $page->siblings();
            }
        },
        'slug' => function (Page $page) {
            return $page->slug();
        },
        'status' => function (Page $page) {
            return $page->status();
        },
        'template' => function (Page $page) {
            return $page->intendedTemplate()->name();
        },
        'title' => function (Page $page) {
            return $page->title()->value();
        },
        'url' => function (Page $page) {
            return $page->previewUrl();
        },
    ],
    'type' => Page::class,
    'views' => [
        'compact' => [
            'id',
            'title',
            'url',
            'num'
        ],
        'default' => [
            'content',
            'id',
            'status',
            'num',
            'options',
            'parent' => 'compact',
            'slug',
            'template',
            'title',
            'url'
        ],
        'panel' => [
            'id',
            'blueprint',
            'status',
            'options',
            'next'    => ['id', 'slug', 'title'],
            'parents' => ['id', 'slug', 'title'],
            'prev'    => ['id', 'slug', 'title'],
            'slug',
            'title',
            'url'
        ],
        'selector' => [
            'id',
            'title',
            'parent' => [
                'id',
                'title'
            ],
            'children' => [
                'id',
                'title',
                'hasChildren'
            ],
        ]
    ],
];
