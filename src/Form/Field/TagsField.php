<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Form\Validations;
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
	use Mixin\Icon;
	use Mixin\Layout;
	use Mixin\Separator;

	/**
	 * If set to `all`, any type of input is accepted. If set to `options` only the predefined options are accepted as input.
	 */
	protected string|null $accept;

	/**
	 * Set to `list` to display each tag with 100% width,
	 * otherwise the tags are displayed inline
	 */
	protected string|null $layout;

	/**
	 * Enable/disable the search in the dropdown
	 * Also limit displayed items (display: 20)
	 * and set minimum number of characters to search (min: 3)
	 */
	protected array|bool|null $search;

	/**
	 * If `true`, selected entries will be sorted
	 * according to their position in the dropdown
	 */
	protected bool|null $sort;

	public function __construct(
		string|null $accept = null,
		string|null $icon = null,
		string|null $layout = null,
		array|bool|null $search = null,
		string|null $separator = null,
		bool|null $sort = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->accept    = $accept;
		$this->icon      = $icon;
		$this->layout    = $layout;
		$this->search    = $search;
		$this->separator = $separator;
		$this->sort      = $sort;
	}

	public function accept(): string
	{
		return match($this->accept) {
			'options' => 'options',
			default   => 'all'
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

	public function icon(): string
	{
		return $this->icon ?? 'tag';
	}

	public function search(): array|bool
	{
		return $this->search ?? true;
	}

	public function sort(): bool
	{
		return $this->sort ?? false;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'accept'    => $this->accept(),
			'icon'      => $this->icon(),
			'layout'    => $this->layout(),
			'search'    => $this->search(),
			'separator' => $this->separator(),
			'sort'      => $this->sort(),
		];
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
