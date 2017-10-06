<?php

use Kirby\Api\Type;

use GraphQL\Type\Definition\InputObjectType;

return function () {

    return new InputObjectType([
        'name' => 'PagesQueryInput',
        'fields' => [
            'parent' => [
                'type'        => Type::string(),
                'description' => 'Show children of this page'
            ],
            'sort' => [
                'type'        => Type::string(),
                'description' => 'Sort pages by this'
            ],
            'pagination' => [
                'type'        => Type::paginationInput(),
                'description' => 'Paginate children in the collection'
            ],
            'filter' => [
                'type'        => Type::listOf(Type::filterInput()),
                'description' => 'Only show children which match all filters'
            ]
        ]
    ]);

};
