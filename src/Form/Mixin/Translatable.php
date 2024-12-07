<?php

namespace Kirby\Form\Mixin;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Translatable
{
	protected bool $translate = true;

	/**
	 * Set the translatable status
	 */
	protected function setTranslate(bool $translate = true): void
	{
		$this->translate = $translate;
	}

	/**
	 * Should the field be translatable?
	 */
	public function translate(): bool
	{
		return $this->translate;
	}
}
