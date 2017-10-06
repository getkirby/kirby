<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Field',
        'fields' => [
            'key'  => [
                'type'    => Type::string(),
                'resolve' => function($field) {
                    return $field->key();
                },
            ],
            'value'  => [
                'type'    => Type::string(),
                'resolve' => function($field) {
                    return $field->value();
                },
            ]
        ]
    ];

};
