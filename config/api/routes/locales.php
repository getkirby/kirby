<?php

/**
 * Locale Routes
 */
return [

    [
        'pattern' => 'locales',
        'method'  => 'GET',
        'action'  => function () {
            return $this->kirby()->locales();
        }
    ],
    [
        'pattern' => 'locales/(:any)',
        'method'  => 'GET',
        'action'  => function (string $code) {
            return $this->kirby()->locales()->find($code);
        }
    ]

];
