<?php

use Kirby\Api\Type;

use GraphQL\Type\Definition\InputObjectType;

return function () {

    return new InputObjectType([
        'name' => 'FilterInput',
        'fields' => [
            'field' => [
                'type' => Type::string()
            ],
            'operator' => [
                'type' => Type::string()
            ],
            'value' => [
                'type' => Type::string()
            ]
        ]
    ]);

};
