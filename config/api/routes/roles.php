<?php

/**
 * Roles Routes
 */
return [
    [
        'pattern' => 'roles',
        'method'  => 'GET',
        'action'  => function () {
            return $this->kirby()->roles();
        }
    ],
    [
        'pattern' => 'roles/(:any)',
        'method'  => 'GET',
        'action'  => function (string $name) {
            return $this->kirby()->roles()->find($name);
        }
    ]
];
