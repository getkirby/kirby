<?php

use Kirby\Exception\Exception;

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

                if (
                    is_a($parent, 'Kirby\Cms\Page') === false &&
                    is_a($parent, 'Kirby\Cms\Site') === false &&
                    is_a($parent, 'Kirby\Cms\File') === false &&
                    is_a($parent, 'Kirby\Cms\User') === false
                ) {
                    throw new Exception('The parent for the section "' . $this->name() . '" has to be a page, site or user object');
                }
            }

            if ($parent === null) {
                return $this->model;
            }

            return $parent;
        }
    ]
];
