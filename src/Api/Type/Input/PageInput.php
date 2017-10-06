<?php

use Kirby\Api\Type;

use GraphQL\Type\Definition\InputObjectType;

return function () {

    return new InputObjectType([
        'name' => 'PageInput',
        'fields' => [
            'id' => [
                'type' => Type::string(),
                'description' => 'The page id'
            ],
            'template' => [
                'type' => Type::string(),
                'description' => 'The name of the page template'
            ],
            'content' => [
                'type' => Type::listOf(Type::fieldInput()),
                'description' => 'All the content for the page as array with keys and values'
            ]
        ]
    ]);

};
