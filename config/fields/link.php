<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

return [
	'props' => [
		'after'       => null,
		'before'      => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * @values 'anchor', 'url, 'page, 'file', 'email', 'tel', 'custom'
		 */
		'options' => function (array|null $options = null): array {
			return $options ?? [
				'url',
				'page',
				'file',
				'email',
				'tel',
				'anchor',
				'custom'
			];
		},
		'value' => function (string|null $value = null) {
			return $value ?? '';
		}
	],
	'methods' => [
		'activeTypes' => function () {
			return array_filter($this->availableTypes(), function (string $type) {
				return in_array($type, $this->props['options']) === true;
			}, ARRAY_FILTER_USE_KEY);
		},
		'availableTypes' => function () {
			return [
				'anchor' => [
					'detect' => function (string $value): bool {
						return Str::startsWith($value, '#') === true;
					},
					'link' => function (string $value): string {
						return $value;
					},
					'validate' => function (string $value): bool {
						return Str::startsWith($value, '#') === true;
					},
				],
				'email' => [
					'detect' => function (string $value): bool {
						return Str::startsWith($value, 'mailto:') === true;
					},
					'link' => function (string $value): string {
						return str_replace('mailto:', '', $value);
					},
					'validate' => function (string $value): bool {
						return V::email($value);
					},
				],
				'file' => [
					'detect' => function (string $value): bool {
						return Str::startsWith($value, 'file://') === true;
					},
					'link' => function (string $value): string {
						return $value;
					},
					'validate' => function (string $value): bool {
						return V::uuid($value, 'file');
					},
				],
				'page' => [
					'detect' => function (string $value): bool {
						return Str::startsWith($value, 'page://') === true;
					},
					'link' => function (string $value): string {
						return $value;
					},
					'validate' => function (string $value): bool {
						return V::uuid($value, 'page');
					},
				],
				'tel' => [
					'detect' => function (string $value): bool {
						return Str::startsWith($value, 'tel:') === true;
					},
					'link' => function (string $value): string {
						return str_replace('tel:', '', $value);
					},
					'validate' => function (string $value): bool {
						return V::tel($value);
					},
				],
				'url' => [
					'detect' => function (string $value): bool {
						return Str::startsWith($value, 'http://') === true || Str::startsWith($value, 'https://') === true;
					},
					'link' => function (string $value): string {
						return $value;
					},
					'validate' => function (string $value): bool {
						return V::url($value);
					},
				],

				// needs to come last
				'custom' => [
					'detect' => function (string $value): bool {
						return true;
					},
					'link' => function (string $value): string {
						return $value;
					},
					'validate' => function (): bool {
						return true;
					},
				]
			];
		},
	],
	'validations' => [
		'value' => function (string|null $value) {
			if (empty($value) === true) {
				return true;
			}

			$detected = false;

			foreach ($this->activeTypes() as $type => $options) {
				if ($options['detect']($value) !== true) {
					continue;
				}

				$link     = $options['link']($value);
				$detected = true;

				if ($options['validate']($link) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.' . $type
					]);
				}
			}

			// none of the configured types has been detected
			if ($detected === false) {
				throw new InvalidArgumentException([
					'key' => 'validation.linkType'
				]);
			}

			return true;
		},
	]
];
