<?php

namespace Kirby\Form\Mixin;

/**
 * Autofocus field functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Autofocus
{
	/**
	 * Sets the focus on this field when the form loads. Only the first field with this label gets
	 */
	protected bool $autofocus;

	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	protected function setAutofocus(bool $autofocus = false): void
	{
		$this->autofocus = $autofocus;
	}
}
