<?php

use Kirby\Api\Type;

return function ($site) {
    return [
        'description' => 'Fetches a File object by path. i.e.: "some/page/example.jpg"',
        'type'        => Type::file(),
        'args'        => [
            'id' => Type::string(),
        ],
        'resolve' => function ($root, $args) use ($site) {
            $id       = dirname($args['id']);
            $filename = basename($args['id']);
            return $site->find($id)->file($filename);
        }
    ];
};
