<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Link field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LinkField extends InputField
{
	/**
	 * @values 'anchor', 'url, 'page, 'file', 'email', 'tel', 'custom'
	 */
	protected array|null $options;

	protected string $value = '';

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		array|string|null $label = null,
		string|null $name = null,
		array|string|null $options = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			name: $name,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->options = $options;
	}

	public function activeTypes(): array
	{
		return array_filter(
			$this->availableTypes(),
			fn (string $type) => in_array($type, $this->options(), true),
			ARRAY_FILTER_USE_KEY
		);
	}

	public function availableTypes(): array
	{
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
	}

	public function options(): array
	{
		// default options
		if ($this->options === null) {
			return ['url', 'page', 'file', 'email', 'tel', 'anchor'];
		}

		// validate options
		$available = array_keys($this->availableTypes());

		if ($unavailable = array_diff($this->options, $available)) {
			throw new InvalidArgumentException(
				key: 'field.link.options',
				data: ['options' => implode(', ', $unavailable)]
			);
		}

		return $this->options;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'options' => $this->options()
		];
	}

	public function validations(): array
	{
		return [
			'option' => $this->validateOption(...)
		];
	}

	protected function validateOption(string|null $value): void
	{
		if (empty($value) === true) {
			return;
		}

		$detected = false;

		foreach ($this->activeTypes() as $type => $options) {
			if ($options['detect']($value) !== true) {
				continue;
			}

			$link     = $options['link']($value);
			$detected = true;

			if ($options['validate']($link) === false) {
				throw new InvalidArgumentException(
					key: 'validation.' . $type
				);
			}
		}

		// none of the configured types has been detected
		if ($detected === false) {
			throw new InvalidArgumentException(
				key: 'validation.linkType'
			);
		}
	}
}
