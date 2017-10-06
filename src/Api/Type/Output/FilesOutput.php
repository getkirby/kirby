<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Files',
        'fields' => [
            'pagination' => [
                'type'    => Type::pagination(),
                'resolve' => function ($files) {
                    return $files->pagination();
                }
            ],
            'items' => [
                'type'    => Type::listOf(Type::file()),
                'resolve' => function ($files) {
                    return $files;
                }
            ]
        ]
    ];

};
