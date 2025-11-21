<?php

namespace Kirby\Form\Mixin;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Help
{
	/**
	 * Optional help text below the field
	 */
	protected array|string|null $help = null;

	public function help(): string|null
	{
		if ($this->help !== null && $this->help !== []) {
			$help = $this->i18n($this->help);
			$help = $this->stringTemplate($help);
			$help = $this->kirby()->kirbytext($help);
			return $help;
		}

		return null;
	}

	protected function setHelp(array|string|null $help): void
	{
		$this->help = $help;
	}
}
