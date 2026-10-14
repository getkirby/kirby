<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\HtmlString;

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
	#[Prop]
	protected array|string|null $text = null;

	public function text(): HtmlString|null
	{
		if ($this->text !== null && $this->text !== [] && $this->text !== '') {
			$text = $this->stringTemplateI18n($this->text);
			$text = $this->kirby()->kirbytext($text);
			return new HtmlString($text);
		}

		return null;
	}
}
