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
		'icon' => function (string|null $icon = null) {
			return $icon;
		},
		/**
		 * Text shown when no option is selected yet
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
