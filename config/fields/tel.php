<?php

use Kirby\Form\Field\TelField;

return [
	'extends' => 'text',
	'proxy' => fn(...$args) => TelField::factory($args),
	'props' => [
		/**
		 * Unset inherited props
		 */
		'converter'  => null,
		'counter'    => null,
		'spellcheck' => null,

		/**
		 * Sets the HTML5 autocomplete attribute
		 */
		'autocomplete' => function (string $autocomplete = 'tel') {
			return $autocomplete;
		},

		/**
		 * Changes the phone icon
		 */
		'icon' => function (string $icon = 'phone') {
			return $icon;
		}
	]
];
