<?php

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'autofocus'   => null,
        'before'      => null,
        'icon'        => null,
        'placeholder' => null,

        /**
         * Default selected user(s) when a new Page/File/User is created
         */
        'default' => function ($default = null) {
            if ($default === false) {
                return [];
            }

            if ($default === null && $user = $this->kirby()->user()) {
                return [
                    $this->userResponse($user)
                ];
            }

            return $this->toUsers($default);
        },
        /**
         * The minimum number of required selected users
         */
        'min' => function (int $min = null) {
            return $min;
        },
        /**
         * The maximum number of allowed selected users
         */
        'max' => function (int $max = null) {
            return $max;
        },
        /**
         * If false, only a single user can be selected
         */
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        },
        'value' => function ($value = null) {
            return $this->toUsers($value);
        },
    ],
    'methods' => [
        'userResponse' => function ($user) {
            $avatar = function ($user) {
                if ($avatar = $user->avatar()) {
                    return [
                        'url' => $avatar->crop(512)->url()
                    ];
                }

                return null;
            };

            return [
                'username' => $user->username(),
                'id'       => $user->id(),
                'email'    => $user->email(),
                'avatar'   => $avatar($user)
            ];
        },
        'toUsers' => function ($value = null) {
            $users = [];
            $kirby = kirby();

            foreach (Yaml::decode($value) as $email) {
                if (is_array($email) === true) {
                    $email = $email['email'] ?? null;
                }

                if ($email !== null && ($user = $kirby->user($email))) {
                    $users[] = $this->userResponse($user);
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
