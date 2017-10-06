<?php

use Kirby\Api\Type;

use GraphQL\Type\Definition\InputObjectType;

return function () {

    return new InputObjectType([
        'name' => 'FieldInput',
        'fields' => [
            'key' => [
                'type'        => Type::string(),
                'description' => 'The name of the field'
            ],
            'value' => [
                'type'        => Type::string(),
                'description' => 'Whatever you want to store with the field'
            ]
        ]
    ]);

};
