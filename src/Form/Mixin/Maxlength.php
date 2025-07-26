<?php

namespace Kirby\Form\Mixin;

/**
 * Maxlength functionality for fields
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
trait Maxlength
{
	/**
	 * Sets the maximum length of the field
	 */
	protected int|null $maxlength;

	public function maxlength(): int|null
	{
		return $this->maxlength;
	}

	protected function setMaxlength(int|null $maxlength = null): void
	{
		$this->maxlength = $maxlength;
	}
}
