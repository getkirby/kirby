<?php

/**
 * Locale Routes
 */
return [

    [
        'pattern' => 'locales',
        'method'  => 'GET',
        'auth'    => false,
        'action'  => function () {
            return $this->kirby()->locales();
        }
    ],
    [
        'pattern' => 'locales/(:any)',
        'method'  => 'GET',
        'auth'    => false,
        'action'  => function (string $code) {
            return $this->kirby()->locales()->find($code);
        }
    ]

];
