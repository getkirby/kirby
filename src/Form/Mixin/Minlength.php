<?php

namespace Kirby\Form\Mixin;

/**
 * Minlength functionality for fields
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
trait Minlength
{
	/**
	 * Sets the minimum length of the field
	 */
	protected int|null $minlength;

	public function minlength(): int|null
	{
		return $this->minlength;
	}

	protected function setMinlength(int|null $minlength = null): void
	{
		$this->minlength = $minlength;
	}
}
