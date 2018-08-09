<?php

/**
 * Roles Routes
 */
return [
    [
        'pattern' => 'languages',
        'method'  => 'GET',
        'action'  => function () {
            return $this->kirby()->languages();
        }
    ],
    [
        'pattern' => 'languages/(:any)',
        'method'  => 'GET',
        'action'  => function (string $code) {
            return $this->kirby()->languages()->find($code);
        }
    ]
];
