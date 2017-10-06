<?php

use Kirby\Api\Type;

return function () {
    return [
        'name' => 'Language',
        'fields' => [
            'name' => [
                'type'    => Type::string(),
                'resolve' => function ($language) {
                    return $language['name'];
                }
            ],
            'locale' => [
                'type'    => Type::string(),
                'resolve' => function ($language) {
                    return $language['locale'];
                }
            ],
            'direction' => [
                'type'    => Type::string(),
                'resolve' => function ($language) {
                    return $language['direction'];
                }
            ]
        ]
    ];
};
