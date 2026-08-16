<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\HtmlString;

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

	public function help(): HtmlString|null
	{
		if ($this->help !== null && $this->help !== [] && $this->help !== '') {
			$help = $this->stringTemplateI18n($this->help);
			$help = $this->kirby()->kirbytext($help);
			return new HtmlString($help);
		}

		return null;
	}
}
