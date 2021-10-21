<?php

use Kirby\Toolkit\Escape;

return [
    'languages' => [
        'pattern' => 'languages',
        'action'  => function () {
            $kirby = kirby();

            return [
                'component' => 'k-languages-view',
                'props'     => [
                    'languages' => $kirby->languages()->values(function ($language) {
                        return [
                            'default' => $language->isDefault(),
                            'id'      => $language->code(),
                            'info'    => Escape::html($language->code()),
                            'text'    => Escape::html($language->name()),
                        ];
                    })
                ]
            ];
        }
    ],
];
