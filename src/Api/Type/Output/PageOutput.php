<?php

use Kirby\Api\Type;
use Kirby\Cms\Page;

return function () {

    return [
        'name'   => 'Page',
        'fields' => function () {
            return [
                'title' => [
                    'type' => Type::string(),
                    'resolve' => function(Page $page) {
                        return $page->title()->value();
                    }
                ],
                'id' => [
                    'type' => Type::string(),
                    'resolve' => function(Page $page) {
                        return $page->id();
                    }
                ],
                'slug' => [
                    'type' => Type::string(),
                    'resolve' => function(Page $page) {
                        return $page->slug();
                    }
                ],
                'num' => [
                    'type' => Type::string(),
                    'resolve' => function(Page $page) {
                        return $page->num();
                    }
                ],
                'template' => [
                    'type' => Type::string(),
                    'resolve' => function(Page $page) {
                        return $page->template();
                    }
                ],
                'url' => [
                    'type' => Type::string(),
                    'resolve' => function(Page $page) {
                        return $page->url();
                    }
                ],
                'files' => [
                    'type'    => Type::listOf(Type::file()),
                    'resolve' => function (Page $page) {
                        return $page->files()->toArray();
                    }
                ],
                'image' => [
                    'type'    => Type::file(),
                    'resolve' => function (Page $page) {
                        return $page->image();
                    }
                ],
                'children' => [
                    'type'    => Type::pages(),
                    'resolve' => function(Page $page, $args = []) {
                        return $page->children();
                    }
                ],
                'hasChildren' => [
                    'type' => Type::boolean(),
                    'resolve' => function (Page $page) {
                        return $page->children()->count() > 0;
                    }
                ],
                'content' => [
                    'type' => Type::listOf(Type::field()),
                    'resolve' => function (Page $page) {
                        return $page->content()->fields();
                    }
                ],
                'parent' => [
                    'type'    => Type::page(),
                    'resolve' => function(Page $page, $args = []) {
                        return $page->parent();
                    }
                ],
                'parents' => [
                    'type'    => Type::listOf(Type::page()),
                    'resolve' => function(Page $page, $args = []) {
                        return $page->parents();
                    }
                ]
            ];
        }
    ];

};
