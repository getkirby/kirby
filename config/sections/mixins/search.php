<?php

use Kirby\Cms\App;

return [
	'props' => [
		/**
		 * Enable/disable the search in the sections
		 */
		'search' => function (bool $search = false): bool {
			return $search;
		}
	],
	'methods' => [
		'searchterm' => function (): string|null {
			if ($this->search === false) {
				return null;
			}

			return App::instance()->request()->get('searchterm');
		}
	]
];
