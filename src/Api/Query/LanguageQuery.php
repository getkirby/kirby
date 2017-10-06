<?php

use Kirby\Api\Type;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;

return function ($languages) {
    return [
        'description' => 'Fetches a Panel language by its locale',
        'type'        => Type::language(),
        'args'        => [
            'locale' => Type::string(),
        ],
        'resolve' => function ($root, $args) use ($languages) {
            $lang = [];

            if ($languages !== null) {
                $json = Data::read($languages . '/' . $args['locale'] . '/package.json');

                $lang['name']      = $json['title'];
                $lang['locale']    = $args['locale'];
                $lang['direction'] = $json['direction'];
            }

            return $lang;
        }
    ];
};
