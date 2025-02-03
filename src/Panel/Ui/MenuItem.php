<?php

namespace Kirby\Panel\Ui;

use Kirby\Exception\Exception;

class MenuItem extends Button
{
	public function __construct(
		string $icon,
		array|string $text,
		bool $current = false,
		string|null $dialog = null,
		bool $disabled = false,
		string|null $drawer = null,
		string|null $link = null,
	) {
		if (
			$dialog === null &&
			$drawer === null &&
			$link === null
		) {
			throw new Exception('You must define a dialog, drawer or link for the menu item');
		}

		parent::__construct(
			current:  $current,
			dialog:   $dialog,
			disabled: $disabled,
			drawer:   $drawer,
			icon:     $icon,
			link:     $link,
			text:     $text
		);
	}

	public function link(): string|null
	{
		if ($this->dialog !== null || $this->drawer !== null) {
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

	public function props(): array
	{
		return [
			...parent::props(),
			'link' => $this->link()
		];
	}
}
