<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\Exception;

return [
	'props' => [
		/**
		 * Sets the query to a parent to find items for the list
		 */
		'parent' => function (string|null $parent = null) {
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
					$parent instanceof Page === false &&
					$parent instanceof Site === false &&
					$parent instanceof File === false &&
					$parent instanceof User === false
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
