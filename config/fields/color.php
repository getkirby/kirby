<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
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
			if (in_array($format, ['hex', 'hsl', 'rgb']) === false) {
				throw new InvalidArgumentException('Unsupported format for color field (supported: hex, rgb, hsl)');
			}

			return $format;
		},
		/**
		 * Change mode to disable the color picker (`input`) or to only
		 * show the `options` as toggles
		 */
		'mode' => function (string $mode = 'picker'): string {
			if (in_array($mode, ['picker', 'input', 'options']) === false) {
				throw new InvalidArgumentException('Unsupported mode for color field (supported: picker, input, options)');
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
			return A::map(array_keys($this->options), fn ($key) => [
				'value' => $this->options[$key],
				'text'  => is_string($key) ? $key : null
			]);
		}
	]
];
