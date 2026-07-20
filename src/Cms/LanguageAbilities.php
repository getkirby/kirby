<?php

namespace Kirby\Cms;

/**
 *Abilities for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageAbilities extends ModelAbilities
{
	public function __construct(
		protected Language $language
	) {
	}

	public function delete(): bool
	{
		// a single-language object cannot be deleted
		if ($this->language->isSingle() === true) {
			return false;
		}

		// the default language can only be deleted if it's the last
		if ($this->language->isDefault() === true && $this->language->isLast() === false) {
			return false;
		}

		return true;
	}
}
