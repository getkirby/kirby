<?php

return [
    'props' => [
        'default' => function ($default = null) {
            return $this->toUsers($default);
        },
        'min' => function (int $min = null) {
            return $min;
        },
        'max' => function (int $max = null) {
            return $max;
        },
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },
        'value' => function ($value = null) {
            return $this->toUsers($value);
        },
    ],
    'methods' => [
        'toUsers' => function ($value = null) {

            $users = [];
            $kirby = kirby();

            foreach (Yaml::decode($value) as $email) {

                if (is_array($email) === true) {
                    $email = $email['email'] ?? null;
                }

                if ($email !== null && ($user = $kirby->user($email))) {
                    $users[] = [
                        'username' => $user->username(),
                        'id'       => $user->id(),
                        'email'    => $user->email(),
                        'avatar'   => [
                            'url'    => $user->avatar()->url(),
                            'exists' => $user->avatar()->exists()
                        ]
                    ];
                }
            }

            return $users;

        }
    ],
    'save' => function ($value = null) {
        return A::pluck($value, 'email');
    },
    'validations' => [
        'max',
        'min'
    ]
];
