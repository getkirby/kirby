<?php

use Kirby\Data\Data;
use Kirby\Toolkit\A;

return [
    'mixins' => ['min', 'picker', 'userpicker'],
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
    'computed' => [
        /**
         * Unset inherited computed
         */
        'default' => null
    ],
    'methods' => [
        'userResponse' => function ($user) {
            return $user->panelPickerData([
                'info'  => $this->info,
                'image' => $this->image,
                'text'  => $this->text,
            ]);
        },
        'toUsers' => function ($value = null) {
            $users = [];
            $kirby = kirby();

            foreach (Data::decode($value, 'yaml') as $email) {
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
    'api' => function () {
        return [
            [
                'pattern' => '/',
                'action' => function () {
                    $field = $this->field();

                    return $field->userpicker([
                        'image'  => $field->image(),
                        'info'   => $field->info(),
                        'limit'  => $field->limit(),
                        'page'   => $this->requestQuery('page'),
                        'query'  => $field->query(),
                        'search' => $this->requestQuery('search'),
                        'text'   => $field->text()
                    ]);
                }
            ]
        ];
    },
    'save' => function ($value = null) {
        return A::pluck($value, 'id');
    },
    'validations' => [
        'max',
        'min'
    ]
];
