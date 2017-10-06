<?php

use Kirby\Api\Type;

use GraphQL\Type\Definition\InputObjectType;

return function () {

    return new InputObjectType([
        'name' => 'PaginationInput',
        'fields' => [
            'limit' => [
                'type'         => Type::int(),
                'defaultValue' => 10,
            ],
            'page' => [
                'type'         => Type::int(),
                'defaultValue' => 1,
            ]
        ]
    ]);

};
