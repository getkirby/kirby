<?php

use Kirby\Api\Type;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;

return function ($languages) {
    return [
        'description' => 'Fetches Panel languages',
        'type'        => Type::listOf(Type::language()),
        'resolve'     => function ($root, $args) use ($languages) {

            $list = [];

            if ($languages !== null) {
                $dir = new Folder($languages);
                foreach ($dir->folders() as $lang) {
                    $json = Data::read($lang . '/package.json');
                    $list[] = [
                        'name'      => $json['title'],
                        'locale'    => basename($lang),
                        'direction' => $json['direction']
                    ];
                }
            }

            return $list;

        }
    ];
};
