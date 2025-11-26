<?php

namespace Kirby\Form\Mixin;

trait Text
{
	/**
	 * Text to be displayed
	 */
	protected array|string|null $text;

	public function text(): string|null
	{
		if ($this->text !== null && $this->text !== [] && $this->text !== '') {
			$text = $this->i18n($this->text);
			$text = $this->stringTemplate($text);
			$text = $this->kirby()->kirbytext($text);
			return $text;
		}

		return null;
	}
}
