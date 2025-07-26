<?php

namespace Kirby\Form\Mixin;

/**
 * Layout functionality for entries
 *
 * @since 6.0.0
 */
trait Layout
{
	/**
	 * Changes the layout of the selected entries.
	 * Available layouts: `list`, `cardlets`, `cards`
	 */
	protected string $layout;

	/**
	 * Layout size for cards: `tiny`, `small`, `medium`, `large`, `huge`, `full`
	 */
	protected string $size;

	public function layout(): string
	{
		return $this->layout;
	}

	public function size(): string
	{
		return $this->size;
	}

	protected function setLayout(string $layout = 'list'): void
	{
		$this->layout = match ($layout) {
			'cards'    => 'cards',
			'cardlets' => 'cardlets',
			default    => 'list'
		};
	}

	protected function setSize(string $size = 'auto'): void
	{
		$this->size = $size;
	}
}
