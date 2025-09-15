<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Translatable
{
	/**
	 * Should the field be translatable?
	 */
	protected bool $translate = true;

	/**
	 * Should the field be translatable into the given language?
	 *
	 * @since 5.0.0
	 */
	public function isTranslatable(Language $language): bool
	{
		if ($this->translate() === false && $language->isDefault() === false) {
			return false;
		}

		return true;
	}

	protected function setTranslate(bool $translate = true): void
	{
		$this->translate = $translate;
	}

	public function translate(): bool
	{
		return $this->translate;
	}
}
