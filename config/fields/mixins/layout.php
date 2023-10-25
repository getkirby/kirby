<?php

return [
	'props' => [
		/**
		 * Changes the layout of the selected entries.
		 * Available layouts: `list`, `cardlets`, `cards`
		 */
		'layout' => function (string $layout = 'list') {
			$layouts = ['list', 'cardlets', 'cards'];
			return in_array($layout, $layouts) ? $layout : 'list';
		},

		/**
		 * Layout size for cards: `tiny`, `small`, `medium`, `large`, `huge`, `full`
		 */
		'size' => function (string $size = 'auto') {
			return $size;
		},
	]
];
