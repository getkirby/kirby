<?php

use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;

return [
    'props' => [
        'default'  => function ($value = null) {
            if ($value === null && $user = App::instance()->user()) {
                return $user->id();
            }

            return $value;
        },
        'icon'     => 'user',
        'options'  => function ($value = null) {
            $options = [];

            foreach (App::instance()->users() as $user) {
                $options[] = [
                    'value' => $user->id(),
                    'text' => $user->name(),
                    'image' => $user->avatar()->exists() ? $user->avatar()->url() : null
                ];
            }

            return $options;
        },
        'required' => false
    ],
    'methods' => [
        'validate' => function () {
            $this->validate('required');

            // Check if value represents existing user
            if ($this->isEmpty() === false) {
                if (App::instance()->user($this->value()) === null) {
                    throw new NotFoundException([
                        'key'  => 'user.notFound',
                        'data' => ['name' => $value]
                    ]);
                }
            }
        }
    ]
];
