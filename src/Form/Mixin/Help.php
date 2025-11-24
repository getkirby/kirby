<?php

namespace Kirby\Form\Mixin;

trait Help
{
	/**
	 * Optional help text below the field
	 */
	protected array|string|null $help;

	public function help(): string|null
	{
		if ($this->help !== null && $this->help !== [] && $this->help !== '') {
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
