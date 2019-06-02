<?php

return [
    'mixins' => ['min', 'picker'],
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
         * Default selected user(s) when a new page/file/user is created
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
