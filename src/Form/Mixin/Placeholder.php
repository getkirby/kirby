<?php

namespace Kirby\Form\Mixin;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Placeholder
{
	/**
	 * Optional placeholder value that will be shown when the field is empty
	 */
	protected array|string|null $placeholder;

	public function placeholder(): string|null
	{
		return $this->stringTemplate(
			$this->placeholder
		);
	}

	protected function setPlaceholder(array|string|null $placeholder = null): void
	{
		$this->placeholder = $this->i18n($placeholder);
	}
}
