<?php

namespace Kirby\Form\Mixin;

/**
 * Disabled functionality for fields
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
trait Disabled
{
	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	protected bool $disabled;

	public function disabled(): bool
	{
		return $this->disabled;
	}

	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}
}
