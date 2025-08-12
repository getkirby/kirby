<?php

namespace Kirby\Form\Mixin;

/**
 * Hidden functionality for fields
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
trait Hidden
{
	/**
	 * Sets the hidden state of the field.
	 */
	protected bool $hidden = false;

	protected function setHidden(bool $hidden = false): void
	{
		$this->hidden = $hidden;
	}

	public function hidden(): bool
	{
		return $this->hidden;
	}
}
