<?php

namespace Kirby\Panel\Ui;

use Kirby\Toolkit\I18n;

class MenuItem
{
	public function __construct(
		protected string $icon,
		protected array|string $text,
		protected bool $current = false,
		protected string|null $dialog = null,
		protected bool $disabled = false,
		protected string|null $drawer = null,
		protected string|null $link = null,
	) {
	}

	public function current(): bool
	{
		return $this->current;
	}

	public function dialog(): string|null
	{
		return $this->dialog;
	}

	public function disabled(): bool
	{
		return $this->disabled;
	}

	public function drawer(): string|null
	{
		return $this->drawer;
	}

	public function icon(): string
	{
		return $this->icon;
	}

	public function link(): string|null
	{
		if ($this->dialog || $this->drawer) {
			return null;
		}

		return $this->link;
	}

	/**
	 * Set additional props from an array
	 */
	public function merge(array $props): static
	{
		foreach ($props as $key => $value) {
			$this->{$key} = $value;
		}

		return $this;
	}

	/**
	 * Returns the translated button text
	 */
	public function text(): string
	{
		return I18n::translate($this->text, $this->text);
	}

	/**
	 * Returns all props for the menu button
	 */
	public function toArray(): array
	{
		return array_filter([
			'current'  => $this->current(),
			'dialog'   => $this->dialog(),
			'disabled' => $this->disabled(),
			'drawer'   => $this->drawer(),
			'icon'     => $this->icon(),
			'link'     => $this->link(),
			'text'     => $this->text(),
		]);
	}
}
