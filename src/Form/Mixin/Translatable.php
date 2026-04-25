<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;

/**
 * Provides the `translate` prop to mark the field as translatable
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Translatable
{
	/**
	 * Should the field be translatable?
	 */
	protected bool|null $translate;

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

	public function translate(): bool
	{
		return $this->translate ?? true;
	}
}
