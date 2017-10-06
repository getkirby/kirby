<?php

use Kirby\Api\Type;
use Kirby\Toolkit\Str;

return function ($site) {
    return [
        'description' => 'Fetches files of a particular page',
        'type'        => Type::files(),
        'args'        => [
            'query' => [
                'type' => Type::filesQueryInput()
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {

            $query = $args['query'];

            if (empty($query['parent']) || $query['parent'] === '/') {
                $files = $site->files();
            } else {
                $files = $site->children()->find($query['parent'])->files();
            }

            if (!empty($query['sort'])) {
                $files = $files->sortBy(...Str::split($query['sort']));
            }

            if (!empty($query['filter'])) {
                foreach ($query['filter'] as $filter) {
                    $files = $files->filterBy($filter['field'], $filter['operator'], $filter['value']);
                }
            }

            return $files->paginate($query['pagination']['limit'] ?? 10, $query['pagination']['page'] ?? 1);

        }
    ];
};
