<?php

namespace Kirby\Form\Mixin;

use Kirby\Form\FieldOptions;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;

/**
 * Provides options loading and resolution for choice-based fields
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Options
{
	/**
	 * An array with options
	 */
	#[Prop(computed: true)]
	protected array|string|null $options = null;

	/**
	 * Cache for the source of the options
	 */
	protected FieldOptions|null $optionsCache = null;

	/**
	 * Creates the source of the options
	 */
	protected function fieldOptions(): FieldOptions
	{
		$props = FieldOptions::polyfill(['options' => $this->options ?? []]);
		return FieldOptions::factory($props['options']);
	}

	public function hasOptionValue(mixed $value): bool
	{
		// convert all available values to strings
		// to be able to compare them easily
		$values = array_map(
			fn (mixed $value) => (string)$value,
			$this->optionValues()
		);

		return in_array((string)$value, $values, true);
	}

	/**
	 * Returns the resolved options; models that the current user
	 * must not list only show up as placeholders for the field's values
	 *
	 * @return list<array<string, mixed>>
	 */
	public function options(): array
	{
		$this->optionsCache ??= $this->fieldOptions();

		return $this->optionsCache->render(
			model: $this->model(),
			values: [
				...A::wrap($this->toFormValue()),
				...A::wrap($this->default())
			]
		);
	}

	public function optionValues(): array
	{
		return array_column($this->options(), 'value');
	}
}
