<?php

namespace Kirby\Form\Mixin;

/**
 * Empty state functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait EmptyState
{
	/**
	 * Sets the text for the empty state box
	 */
	protected string|null $empty;

	protected function setEmpty(string|array|null $empty = null): void
	{
		$this->empty = $this->i18n($empty);
	}

	public function empty(): string|null
	{
		return $this->stringTemplate($this->empty);
	}
}
