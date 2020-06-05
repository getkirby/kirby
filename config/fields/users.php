<?php

return [
    'mixins' => [
        'min',
        'picker',
        'preview',
        'userpicker'
    ],
    'props' => [
        /**
         * Default selected user(s) when a new page/file/user is created
         */
        'default' => function ($default = null) {
            if ($default === false) {
                return [];
            }

            if (
                $default === null &&
                $user = $this->kirby()->user()
            ) {
                return [
                    $this->userResponse($user)
                ];
            }

            return $default;
        }
    ],
    'methods' => [
        'toModel' => function (string $id = null) {
            return $this->kirby()->user($id);
        }
    ],
    'api' => function () {
        return [
            [
                'pattern' => 'items',
                'action'  => function () {
                    $field = $this->field();
                    $ids   = Str::split($this->requestQuery('ids'));
                    return $field->toModels($ids);
                }
            ],
            [
                'pattern' => 'options',
                // TODO: the following line should not be needed,
                // but removing it causes errors
                'method' => 'GET',
                'action' => function () {
                    $field = $this->field();

                    return $field->userpicker([
                        'info'    => $field->info(),
                        'limit'   => $field->limit(),
                        'page'    => $this->requestQuery('page'),
                        'preview' => $field->preview(),
                        'query'   => $field->query(),
                        'search'  => $this->requestQuery('search'),
                        'text'    => $field->text()
                    ]);
                }
            ]
        ];
    }
];
