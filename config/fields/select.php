<?php

return [
	'extends' => 'radio',
	'props' => [
		/**
		 * Unset inherited props
		 */
		'columns' => null,

		/**
		 * Custom icon to replace the arrow down.
		 */
		'icon' => function (string $icon = null) {
			return $icon;
		},
		/**
		 * Custom placeholder string for empty option.
		 */
		'placeholder' => function (string $placeholder = '—') {
			return $placeholder;
		},
	]
];
