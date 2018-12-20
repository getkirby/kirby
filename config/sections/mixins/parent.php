<?php

use Kirby\Toolkit\Str;

return [
    'methods' => [
        'parent' => function () {
            $parent = $this->parent;

            if (is_string($parent) === true) {
                $parent = $this->model->query($parent);
            }

            if ($parent === null) {
                return $this->model;
            }

            return $parent;
        }
    ]
];
