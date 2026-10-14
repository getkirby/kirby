<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;

/**
 * Writer Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class WriterField extends ProseMirrorField
{
	use Mixin\Counter;

	/**
	 * Available heading levels
	 */
	#[Prop]
	protected array|null $headings = [1, 2, 3, 4, 5, 6];

	/**
	 * Enables inline mode, which will not wrap new lines in paragraphs and creates hard breaks instead.
	 */
	#[Prop]
	protected bool $inline = false;

	public function headings(): array
	{
		return array_intersect($this->headings, range(1, 6));
	}

	public function inline(): bool
	{
		return $this->inline;
	}
}
