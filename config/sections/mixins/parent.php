<?php

use Kirby\Toolkit\Str;

return [
    'props' => [
        /**
         * Sets the query to a parent to find items for the list
         */
        'parent' => function (string $parent = null) {
            return $parent;
        }
    ],
    'methods' => [
        'parentModel' => function () {
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
