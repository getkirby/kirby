<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Sane\Sane;
use Kirby\Toolkit\V;

return [
	'props' => [
		/**
		 * Enables/disables the character counter in the top right corner
		 */
		'counter' => function (bool $counter = true) {
			return $counter;
		},
		/**
		 * Available heading levels
		 */
		'headings' => function (array|null $headings = null) {
			return array_intersect($headings ?? range(1, 6), range(1, 6));
		},
		/**
		 * Enables inline mode, which will not wrap new lines in paragraphs and creates hard breaks instead.
		 *
		 * @param bool $inline
		 */
		'inline' => function (bool $inline = false) {
			return $inline;
		},
		/**
		 * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`, `email`. Activate/deactivate them all by passing `true`/`false`. Default marks are `bold`, `italic`, `underline`, `strike`, `link`, `email`
		 * @param array|bool $marks
		 */
		'marks' => function ($marks = null) {
			return $marks;
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
		 * Sets the allowed nodes. Available nodes: `paragraph`, `heading`, `bulletList`, `orderedList`, `quote`. Activate/deactivate them all by passing `true`/`false`. Default nodes are `paragraph`, `heading`, `bulletList`, `orderedList`.
		 * @param array|bool|null $nodes
		 */
		'nodes' => function ($nodes = null) {
			return $nodes;
		},
		/**
		 * Toolbar options, incl. `marks` (to narrow down which marks should have toolbar buttons), `nodes` (to narrow down which nodes should have toolbar dropdown entries) and `inline` to set the position of the toolbar (false = sticking on top of the field)
		 */
		'toolbar' => function ($toolbar = null) {
			return $toolbar;
		}
	],
	'computed' => [
		'value' => function () {
			$value = trim($this->value ?? '');
			$value = Sane::sanitize($value, 'html');

			// convert non-breaking spaces to HTML entity
			// as that's how ProseMirror handles it internally;
			// will allow comparing saved and current content
			$value = str_replace(' ', '&nbsp;', $value);

			return $value;
		}
	],
	'validations' => [
		'minlength' => function ($value) {
			if (
				$this->minlength &&
				V::minLength(strip_tags($value), $this->minlength) === false
			) {
				throw new InvalidArgumentException([
					'key' => 'validation.minlength',
					'data' => ['min' => $this->minlength]
				]);
			}
		},
		'maxlength'  => function ($value) {
			if (
				$this->maxlength &&
				V::maxLength(strip_tags($value), $this->maxlength) === false
			) {
				throw new InvalidArgumentException([
					'key' => 'validation.maxlength',
					'data' => ['max' => $this->maxlength]
				]);
			}
		},
	]
];
