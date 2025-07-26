<?php

namespace Kirby\Form\Mixin;

/**
 * Max functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Max
{
	/**
	 * Sets the maximum number of allowed items in the field
	 */
	protected int|null $max;

	public function max(): int|null
	{
		return $this->max;
	}

	protected function setMax(int|null $max = null)
	{
		$this->max = $max;
	}
}
