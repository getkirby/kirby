<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `help` prop for optional help text below the field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Help
{
	/**
	 * Optional help text below the field
	 */
	protected array|string|null $help;

	public function help(): string|null
	{
		if ($this->help !== null && $this->help !== [] && $this->help !== '') {
			$help = $this->stringTemplateI18n($this->help);
			$help = $this->kirby()->kirbytext($help);
			return $help;
		}

		return null;
	}
}
