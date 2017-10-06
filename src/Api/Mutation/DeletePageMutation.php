<?php

use Kirby\Api\Type;
use Kirby\Toolkit\Str;

return function ($site) {
    return [
        'description' => 'Deletes a page',
        'type'        => Type::string(),
        'args'        => [
            'id' => [
                'type'     => Type::string(),
                'required' => true,
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {

            $page = $site->find($args['id']);

            if ($page === null) {
                throw new Exception(sprintf('The page "%s" cannot be found', $args['id']));
            }

            $page->delete();

            return 'ok';

        }
    ];
};
