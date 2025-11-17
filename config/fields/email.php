<?php

use Kirby\Toolkit\I18n;
use Kirby\Form\Field\EmailField;

return [
	'extends' => 'text',
	'proxy' => fn(...$args) => EmailField::factory($args),
	'props' => [
		/**
		 * Unset inherited props
		 */
		'converter' => null,
		'counter'   => null,

		/**
		 * Sets the HTML5 autocomplete mode for the input
		 */
		'autocomplete' => function (string $autocomplete = 'email') {
			return $autocomplete;
		},

		/**
		 * Changes the email icon to something custom
		 */
		'icon' => function (string $icon = 'email') {
			return $icon;
		},

		/**
		 * Custom placeholder text, when the field is empty.
		 */
		'placeholder' => function ($value = null) {
			return $value;
		}
	],
	'validations' => [
		'minlength',
		'maxlength',
		'email'
	]
];
