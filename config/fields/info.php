<?php

use Kirby\Form\Field\InfoField;

return [
	'proxy' => function (...$attrs) {
		return InfoField::factory($attrs);
	},
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'autofocus'   => null,
		'before'      => null,
		'default'     => null,
		'disabled'    => null,
		'placeholder' => null,
		'required'    => null,
		'translate'   => null,

		/**
		 * Text to be displayed
		 */
		'text' => function ($value = null) {
			return $value;
		},

		/**
		 * Change the design of the info box
		 */
		'theme' => function (string|null $theme = null) {
			return $theme;
		}
	],
	'save' => false,
];
