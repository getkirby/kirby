<?php

namespace Kirby\Form\Mixin;

/**
 * After field functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait After
{
	/**
	 * Optional text that will be shown after the input
	 */
	protected string|null $after;

	public function after(): string|null
	{
		return $this->stringTemplate($this->after);
	}

	protected function setAfter(array|string|null $after = null): void
	{
		$this->after = $this->i18n($after);
	}
}
