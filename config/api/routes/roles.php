<?php

/**
 * Roles Routes
 */
return [
    [
        'pattern' => 'roles',
        'method'  => 'GET',
        'action'  => function () {
            switch (get('canBe')) {
                case 'changed':
                    return $this->kirby()->roles()->canBeChanged();
                case 'created':
                    return $this->kirby()->roles()->canBeCreated();
                default:
                    return $this->kirby()->roles();
            }
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
