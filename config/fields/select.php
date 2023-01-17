<?php

use Kirby\Field\FieldOptions;

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
	],
	'methods' => [
		'getOptions' => function () {
			$props = FieldOptions::polyfill($this->props);

			// disable safe mode as the select field does not
			// render HTML for the option text
			$options = FieldOptions::factory($props['options'], false);

			return $options->render($this->model());
		}
	]
];
