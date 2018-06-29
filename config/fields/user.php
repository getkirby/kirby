<?php

use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;

return [
    'props' => [
        'icon' => function (string $icon = 'user') {
            return $icon;
        },
        'value' => function ($value = null) {
            if (empty($value) === true) {
                return null;
            }

            if (is_array($value) && isset($value['email']) === true) {
                return $value;
            }

            if ($user = App::instance()->user($value)) {
                return $this->userData($user);
            }
        }
    ],
    'computed' => [
        // TODO: find a better way to solve this
        // 'default' => function () {
        //     if ($this->props['default']) {
        //         return $this->props['default'];
        //     }

        //     if ($user = App::instance()->user()) {
        //         return $user->id();
        //     }
        // },
        'users' => function () {
            $options = [];

            foreach (App::instance()->users() as $user) {
                $options[] = $this->userData($user);
            }

            return $options;
        },
    ],
    'methods' => [
        'userData' => function ($user) {
            return [
                'email' => $user->email(),
                'name'  => $name = $user->name() ?? $user->email(),
                'text'  => $name,
                'image' => $user->avatar()->exists() ? $user->avatar()->url() : null,
                'icon'  => 'user'
            ];
        },
        'toString' => function ($value) {
            return $value['email'] ?? '';
        }
    ],
    'validations' => [
        'required',
        'exists' => function ($value) {
            // Check if value represents existing user
            if ($this->isEmpty($value) === false) {
                if (isset($value['email']) === false || App::instance()->user($value['email']) === null) {
                    throw new NotFoundException([
                        'key'  => 'user.notFound',
                        'data' => ['name' => $value]
                    ]);
                }
            }
        }
    ]
];
