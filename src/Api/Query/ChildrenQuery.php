<?php

use Kirby\Api\Type;
use Kirby\Toolkit\Str;

return function ($site) {
    return [
        'description' => 'Fetches children of a particular page',
        'type'        => Type::pages(),
        'args'        => [
            'query' => [
                'type' => Type::pagesQueryInput(),
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {

            $query = $args['query'];

            if (empty($query['parent']) || $query['parent'] === '/') {
                $pages = $site->children();
            } else {
                $pages = $site->children()->find($query['parent'])->children();
            }

            if (!empty($query['sort'])) {
                $pages = $pages->sortBy(...Str::split($query['sort']));
            }

            if (!empty($query['filter'])) {
                foreach ($query['filter'] as $filter) {
                    $pages = $pages->filterBy($filter['field'], $filter['operator'], $filter['value']);
                }
            }

            return $pages->paginate($query['pagination']['limit'], $query['pagination']['page']);

        }
    ];
};
