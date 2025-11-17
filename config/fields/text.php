<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\TextField;

return [
	'proxy' => fn(...$args) => TextField::factory($args),
	'props' => [

		/**
		 * The field value will be converted with the selected converter before the value gets saved. Available converters: `lower`, `upper`, `ucfirst`, `slug`
		 */
		'converter' => function ($value = null) {
			if (
				$value !== null &&
				array_key_exists($value, $this->converters()) === false
			) {
				throw new InvalidArgumentException(
					key: 'field.converter.invalid',
					data: ['converter' => $value]
				);
			}

			return $value;
		},

		/**
		 * Shows or hides the character counter in the top right corner
		 */
		'counter' => function (bool $counter = true) {
			return $counter;
		},

		/**
		 * Sets the font family (sans or monospace)
		 */
		'font' => function (string|null $font = null) {
			return $font;
		},

		/**
		 * Maximum number of allowed characters
		 */
		'maxlength' => function (int|null $maxlength = null) {
			return $maxlength;
		},

		/**
		 * Minimum number of required characters
		 */
		'minlength' => function (int|null $minlength = null) {
			return $minlength;
		},

		/**
		 * A regular expression, which will be used to validate the input
		 */
		'pattern' => function (string|null $pattern = null) {
			return $pattern;
		},

		/**
		 * If `false`, spellcheck will be switched off
		 */
		'spellcheck' => function (bool $spellcheck = false) {
			return $spellcheck;
		},
	],
	'computed' => [
		'value' => function () {
			return (string)$this->convert($this->value);
		}
	],
	'validations' => [
		'minlength',
		'maxlength',
		'pattern'
	]
];
