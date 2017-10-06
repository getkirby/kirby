<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Pages',
        'fields' => [
            'pagination' => [
                'type'    => Type::pagination(),
                'resolve' => function ($pages) {
                    return $pages->pagination();
                }
            ],
            'items' => [
                'type'    => Type::listOf(Type::page()),
                'resolve' => function ($pages) {
                    return $pages;
                }
            ]
        ]
    ];

};
