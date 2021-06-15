<?php

use Kirby\Cms\Find;

return [
    'users/(:any)/changeName' => [
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'name' => $user->username()
                ]
            ];
        },
        'submit' => function (string $id) {
        }
    ]
];
