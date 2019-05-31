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
                $query  = $parent;
                $parent = $this->model->query($query);

                if (!$parent) {
                    throw new Exception('The parent for the query "' . $query . '" cannot be found in the section "' . $this->name() . '"');
                }
            }

            if ($parent === null) {
                return $this->model;
            }

            return $parent;
        }
    ]
];
