<?php

use Kirby\Api\Type;

use GraphQL\Type\Definition\InputObjectType;

return function () {

    return new InputObjectType([
        'name' => 'FilesQueryInput',
        'fields' => [
            'parent' => [
                'type'        => Type::string(),
                'description' => 'Show files of this page'
            ],
            'sort' => [
                'type'        => Type::string(),
                'description' => 'Sort files by this'
            ],
            'pagination' => [
                'type'        => Type::paginationInput(),
                'description' => 'Paginate files in the collection'
            ],
            'filter' => [
                'type'        => Type::listOf(Type::filterInput()),
                'description' => 'Only show files which match all filters'
            ]
        ]
    ]);

};
