<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Form\Validations;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Tags Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TagsField extends OptionsField
{
	use Mixin\Separator;

	/**
	 * If set to `all`, any type of input is accepted. If set to `options` only the predefined options are accepted as input.
	 */
	#[Prop]
	protected string $accept = 'all';

	/**
	 * Icon that will be shown at the end of the field
	 */
	#[Prop]
	protected string|null $icon = 'tag';

	/**
	 * Set to `list` to display each tag with 100% width,
	 * otherwise the tags are displayed inline
	 */
	#[Prop]
	protected string|null $layout = null;

	/**
	 * Enable/disable the search in the dropdown
	 * Also limit displayed items (display: 20)
	 * and set minimum number of characters to search (min: 3)
	 */
	#[Prop]
	protected array|bool $search = true;

	/**
	 * If `true`, selected entries will be sorted
	 * according to their position in the dropdown
	 */
	#[Prop]
	protected bool $sort = false;

	public function accept(): string
	{
		return match ($this->accept) {
			'all', 'options' => $this->accept,
			default          => 'all'
		};
	}

	public function default(): array
	{
		return parent::default() ?? [];
	}

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: Str::split($value, $this->separator())
		);
	}

	public function icon(): string|null
	{
		return $this->icon;
	}

	public function layout(): string|null
	{
		return $this->layout;
	}

	public function search(): array|bool
	{
		return $this->search;
	}

	public function sort(): bool
	{
		return $this->sort;
	}

	public function toStoredValue(): string
	{
		return A::join($this->value, $this->separator() . ' ');
	}

	protected function validations(): array
	{
		return [
			'max',
			'min',
			'accepted' => fn ($value) => $this->validateAcceptedOptions($value)
		];
	}

	protected function validateAcceptedOptions(array $value): void
	{
		if ($this->accept() === 'all' || $value === []) {
			return;
		}

		Validations::options($this, $value);
	}
}
