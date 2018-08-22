<?php

use Kirby\Toolkit\Str;

return [
    'methods' => [
        'parent' => function () {

            $parent = $this->parent;

            if (is_string($parent) === true) {
                $parent = Str::query($parent, [
                    'kirby' => $kirby = kirby(),
                    'site'  => $kirby->site()
                ]);
            }

            if ($parent === null) {
                return $this->model;
            }

            return $parent;

        }
    ]
];
