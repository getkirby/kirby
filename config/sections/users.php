<?php

use Kirby\Toolkit\Str;
use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'empty',
        'headline',
        'help',
        'layout',
        'min',
        'max',
        'pagination'
    ],
    'props' => [
        /**
         * Enables/disables user creating
         */
        'create' => function (bool $create = true) {
            return $create;
        },
        /**
         * Enables/disables reverse sorting
         */
        'flip' => function (bool $flip = false) {
            return $flip;
        },
        /**
         * Image options to control the source and look of user previews
         */
        'image' => function ($image = null) {
            return $image ?? [];
        },
        /**
         * Optional info text setup. Info text is shown on the right (lists) or below (cards) the user name.
         */
        'info' => function (string $info = null) {
            return $info;
        },
        /**
         * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },
        /**
         * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. `date desc`)
         */
        'sortBy' => function (string $sortBy = null) {
            return $sortBy;
        },
        /**
         * Filters the list by roles
         */
        'roles' => function ($roles = null) {
            return $roles;
        },
        /**
         * Setup for the main text in the list or cards. By default this will display the user name.
         */
        'text' => function (string $text = '{{ user.username }}') {
            return $text;
        },
        /**
         * Optional query to select a specific set of users
         */
        'query' => function (string $query = null) {
            return $query;
        },
    ],
    'computed' => [
        'users' => function () {
            if (empty($this->query) === false) {
                $users = Str::query($this->query, [
                    'kirby' => $this->kirby(),
                    'site'  => $this->site()
                ]);

                if (is_a($users, 'Kirby\Cms\Users') === false) {
                    $users = $this->kirby()->users();
                }
            } else {
                $users = $this->kirby()->users();
            }

            // roles
            if (empty($this->roles) === false) {
                $roles = is_array($this->roles) === true ? $this->roles : [$this->roles];
                $users = $users->filterBy('role', 'in', $roles);
            }

            // sort
            if ($this->sortBy) {
                $users = $users->sortBy(...$users::sortArgs($this->sortBy));
            }

            // flip
            if ($this->flip === true) {
                $users = $users->flip();
            }

            // pagination
            $users = $users->paginate([
                'page'  => $this->page,
                'limit' => $this->limit
            ]);

            return $users;
        },
        'total' => function () {
            return $this->users->pagination()->total();
        },
        'data' => function () {
            $data = [];

            foreach ($this->users as $id) {
                $user        = $this->kirby()->user($id);
                $image       = $user->panelImage($this->image);

                $data[] = [
                    'id'     => $user->id(),
                    'text'   => $user->toString($this->text),
                    'info'   => $user->toString($this->info ?? false),
                    'icon'   => $user->panelIcon($image),
                    'image'  => $image,
                    'link'   => $user->panelUrl(true),
                    'status' => $user->status()
                ];
            }

            return $data;
        },
        'errors' => function () {
            $errors = [];

            if ($this->validateMax() === false) {
                $errors['max'] = I18n::template('error.section.users.max.' . I18n::form($this->max), [
                    'max'     => $this->max,
                    'section' => $this->headline
                ]);
            }

            if ($this->validateMin() === false) {
                $errors['min'] = I18n::template('error.section.users.min.' . I18n::form($this->min), [
                    'min'     => $this->min,
                    'section' => $this->headline
                ]);
            }

            if (empty($errors) === true) {
                return [];
            }

            return [
                $this->name => [
                    'label'   => $this->headline,
                    'message' => $errors,
                ]
            ];
        },
        'add' => function () {
            if ($this->create === false) {
                return false;
            }

            if ($this->isFull() === true) {
                return false;
            }

            return true;
        },
        'link' => function () {
            return '/users';
        },
        'pagination' => function () {
            return $this->pagination();
        }
    ],
    'toArray' => function () {
        return [
            'data'    => $this->data,
            'errors'  => $this->errors,
            'options' => [
                'add'      => $this->add,
                'empty'    => $this->empty,
                'headline' => $this->headline,
                'help'     => $this->help,
                'layout'   => $this->layout,
                'link'     => $this->link,
                'max'      => $this->max,
                'min'      => $this->min,
                'size'     => $this->size
            ],
            'pagination' => $this->pagination,
        ];
    }
];
