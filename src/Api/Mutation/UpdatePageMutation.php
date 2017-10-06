<?php

use Kirby\Api\Type;
use Kirby\Toolkit\Str;

return function ($site) {
    return [
        'description' => 'Updates the data of an existing page',
        'type'        => Type::page(),
        'args'        => [
            'page' => [
                'type'     => Type::pageInput(),
                'required' => true,
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {

            $page = $site->find($args['page']['id']);

            if ($page === null) {
                throw new Exception(sprintf('The page "%s" cannot be found'), $args['page']['id']);
            }

            $content = [];

            foreach ($args['page']['content'] as $field) {
                $content[$field['key']] = $field['value'];
            }

            $page->update($content);

            return $page;

        }
    ];
};
