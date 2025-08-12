<?php

namespace Kirby\Form\Mixin;

/**
 * Counter functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 * @since 6.0.0
 *
 * @package   Kirby Form
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Counter
{
	/**
	 * The text to show before the counter
	 */
	protected bool $counter;

	public function counter(): bool
	{
		return $this->counter;
	}

	protected function setCounter(bool $counter = true): void
	{
		$this->counter = $counter;
	}
}
