<?php

return [
	'extends' => 'tags',
	'props' => [
		/**
		 * Unset inherited props
		 */
		'accept' => null,
		/**
		 * Custom icon to replace the arrow down.
		 */
		'icon' => function (string $icon = null) {
			return $icon;
		},
		/**
		 * Enable/disable the search in the dropdown
		 * Also limit displayed items (display: 20)
		 * and set minimum number of characters to search (min: 3)
		 */
		'search' => function ($search = true) {
			return $search;
		},
		/**
		 * If `true`, selected entries will be sorted
		 * according to their position in the dropdown
		 */
		'sort' => function (bool $sort = false) {
			return $sort;
		},
	]
];
