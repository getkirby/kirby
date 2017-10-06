<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Pagination',
        'fields' => [
            'total' => [
                'type'    => Type::int(),
                'resolve' => function ($pagination) {
                    return $pagination->total();
                }
            ],
            'limit' => [
                'type'    => Type::int(),
                'resolve' => function ($pagination) {
                    return $pagination->limit();
                }
            ],
            'page' => [
                'type'    => Type::int(),
                'resolve' => function ($pagination) {
                    return $pagination->page();
                }
            ]
        ]
    ];

};
