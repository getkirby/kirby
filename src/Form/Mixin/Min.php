<?php

namespace Kirby\Form\Mixin;

/**
 * Min functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Min
{
	/**
	 * Sets the minimum number of required items in the field
	 */
	protected int|null $min;

	public function min(): int|null
	{
		// set min to at least 1, if required
		if ($this->required === true) {
			return $this->min ?? 1;
		}

		return $this->min;
	}

	protected function setMin(int|null $min = null)
	{
		$this->min = $min;
	}

	public function isRequired(): bool
	{
		// set required to true if min is set
		if ($this->min) {
			return true;
		}

		return $this->required;
	}
}
