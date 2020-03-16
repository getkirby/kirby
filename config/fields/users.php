<?php

use Kirby\Data\Yaml;
use Kirby\Toolkit\A;

return [
    'mixins' => [
        'min',
        'picker',
        'userpicker'
    ],
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
            $response = $user->panelPickerData([
                'info'  => $this->info,
                'image' => $this->image,
                'text'  => $this->text,
            ]);

            if ($this->primaryKey !== null && empty($response[$this->primaryKey]) === true) {
                $response[$this->primaryKey] = $user->content()->get($this->primaryKey)->value();
            }

            return $response;
        },
        'toUsers' => function ($value = null) {
            $users = [];

            foreach (Yaml::decode($value) as $data) {
                $id = is_array($data) === true ? ($data['email'] ?? null) : $data;

                if (empty($id) === false) {
                    if ($this->primaryKey === null || is_array($data) === true) {
                        $user = $this->kirby()->user($id);
                    } else {
                        $user = $this->kirby()->users()->findBy($this->primaryKey, $id);
                    }

                    if ($user) {
                        $users[] = $this->userResponse($user);
                    }
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
        return A::pluck($value, $this->primaryKey ?? 'id');
    },
    'validations' => [
        'max',
        'min'
    ]
];
