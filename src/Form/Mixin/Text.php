<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `text` prop for displayable text content
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Text
{
	/**
	 * Text to be displayed
	 */
	protected array|string|null $text;

	public function text(): string|null
	{
		if ($this->text !== null && $this->text !== [] && $this->text !== '') {
			$text = $this->stringTemplateI18n($this->text);
			$text = $this->kirby()->kirbytext($text);
			return $text;
		}

		return null;
	}
}
