<?php

use Kirby\Cms\Helpers;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Field\FieldOptions;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Str;

return [
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,

		/**
		 * Whether to allow alpha transparency in the color
		 */
		'alpha' => function (bool $alpha = false) {
			return $alpha;
		},
		/**
		 * The CSS format (hex, rgb, hsl) to display and store the value
		 */
		'format' => function (string $format = 'hex'): string {
			if (in_array($format, ['hex', 'hsl', 'rgb'], true) === false) {
				throw new InvalidArgumentException(
					message: 'Unsupported format for color field (supported: hex, rgb, hsl)'
				);
			}

			return $format;
		},
		/**
		 * Change mode to disable the color picker (`input`) or to only
		 * show the `options` as toggles
		 */
		'mode' => function (string $mode = 'picker'): string {
			if (in_array($mode, ['picker', 'input', 'options'], true) === false) {
				throw new InvalidArgumentException(
					message: 'Unsupported mode for color field (supported: picker, input, options)'
				);
			}

			return $mode;
		},
		/**
		 * List of colors that will be shown as buttons
		 * to directly select them
		 */
		'options' => function (array $options = []): array {
			return $options;
		}
	],
	'computed' => [
		'default' => function (): string {
			return Str::lower($this->default);
		},
		'options' => function (): array {
			// resolve options to support manual arrays
			// alongside api and query options
			$props   = FieldOptions::polyfill($this->props);
			$options = FieldOptions::factory([
				'text'  => '{{ item.value }}',
				'value' => '{{ item.key }}',
				...$props['options']
			]);

			$options = $options->render($this->model());

			if (empty($options) === true) {
				return [];
			}

			if (
				is_numeric($options[0]['value']) ||
				$options[0]['value'] === $options[0]['text']
			) {
				// simple array of values
				// or value=text (from Options class)
				$options = A::map($options, fn ($option) => [
					'value' => $option['text']
				]);

			} elseif ($this->isColor($options[0]['text'])) {
				// @deprecated 4.0.0
				// TODO: Remove in Kirby 6

				Helpers::deprecated('Color field "' . $this->name . '": the text => value notation for options has been deprecated and will be removed in Kirby 6. Please rewrite your options as value => text.');

				$options = A::map($options, fn ($option) => [
					'value' => $option['text'],
					// ensure that any HTML in the new text is escaped
					'text'  => Escape::html($option['value'])
				]);
			} else {
				$options = A::map($options, fn ($option) => [
					'value' => $option['value'],
					'text'  => $option['text']
				]);
			}

			return $options;
		}
	],
	'methods' => [
		'isColor' => function (string $value): bool {
			return
				$this->isHex($value) ||
				$this->isRgb($value) ||
				$this->isHsl($value);
		},
		'isHex' => function (string $value): bool {
			return preg_match('/^#([\da-f]{3,4}){1,2}$/i', $value) === 1;
		},
		'isHsl' => function (string $value): bool {
			return preg_match('/^hsla?\(\s*(\d{1,3}\.?\d*)(deg|rad|grad|turn)?(?:,|\s)+(\d{1,3})%(?:,|\s)+(\d{1,3})%(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i', $value) === 1;
		},
		'isRgb' => function (string $value): bool {
			return preg_match('/^rgba?\(\s*(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s)+(\d{1,3})(%?)(?:,|\s|\/)*(\d*(?:\.\d+)?)(%?)\s*\)?$/i', $value) === 1;
		},
	],
	'validations' => [
		'color' => function ($value) {
			if (empty($value) === true) {
				return true;
			}

			if ($this->format === 'hex' && $this->isHex($value) === false) {
				throw new InvalidArgumentException(
					key: 'validation.color',
					data: ['format' => 'hex']
				);
			}

			if ($this->format === 'rgb' && $this->isRgb($value) === false) {
				throw new InvalidArgumentException(
					key: 'validation.color',
					data: ['format' => 'rgb']
				);
			}

			if ($this->format === 'hsl' && $this->isHsl($value) === false) {
				throw new InvalidArgumentException(
					key: 'validation.color',
					data: ['format' => 'hsl']
				);
			}
		}
	]
];
