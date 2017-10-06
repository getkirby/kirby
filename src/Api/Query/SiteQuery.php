<?php

use Kirby\Api\Type;

return function ($site) {
    return [
        'description' => 'Fetches all information about the site',
        'type'        => Type::site(),
        'resolve'     => function ($root, $args) use ($site) {
            return $site;
        }
    ];
};
