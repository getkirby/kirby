<?php

use Kirby\Cms\App;

return [
    'props' => [
        /**
         * Enable/disable the search in the sections
         */
        'search' => function (bool $search = false): bool {
            return $search;
        }
    ],
    'computed' => [
        'searchterm' => function (): ?string {
            return App::instance()->request()->get('searchterm');
        }
    ]
];
