<?php

namespace Kirby\Form\Mixin;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Icon
{
	/**
	 * Optional icon that will be shown at the end of the field
	 */
	protected string|null $icon;

	public function icon(): string|null
	{
		return $this->icon;
	}

	protected function setIcon(string|null $icon = null): void
	{
		$this->icon = $icon;
	}
}
