<?php

use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;

return [
    'props' => [
        'icon' => function (string $icon = 'user') {
            return $icon;
        },
        'options' => function () {
            $options = [];

            foreach (App::instance()->users() as $user) {
                $options[] = [
                    'value' => $user->email(),
                    'text'  => $user->name(),
                    'image' => $user->avatar()->exists() ? $user->avatar()->url() : null
                ];
            }

            return $options;
        },
    ],
    'computed' => [
        'default' => function () {
            if ($this->props['default']) {
                return $this->props['default'];
            }

            if ($user = App::instance()->user()) {
                return $user->id();
            }
        },
    ],
    'validations' => [
        'required',
        'exists' => function ($value) {
            // Check if value represents existing user
            if ($this->isEmpty($value) === false) {
                if (App::instance()->user($value) === null) {
                    throw new NotFoundException([
                        'key'  => 'user.notFound',
                        'data' => ['name' => $value]
                    ]);
                }
            }
        }
    ]
];
