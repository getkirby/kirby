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
	protected bool $translate = true;

	/**
	 * Checks if the field can be translated into the
	 * given language
	 */
	public function isTranslatableInto(Language|string $language = 'current'): bool
	{
		$language = Language::ensure($language);

		// fields are always active in the default language
		if ($language->isDefault() === true) {
			return true;
		}

		// for other languages, it depends on the `translate` option
		// so far, this is only a boolean, but could be an array
		// of language codes later
		return $this->translate() === true;
	}

	/**
	 * Checks if the field can be translated into the
	 * currently active language
	 */
	public function isTranslatableIntoCurrentLanguage(): bool
	{
		return $this->isTranslatableInto('current');
	}

	/**
	 * Set the translatable status
	 */
	protected function setTranslate(bool $translate = true): void
	{
		$this->translate = $translate;
	}

	/**
	 * Should the field be translatable?
	 */
	public function translate(): bool
	{
		return $this->translate;
	}
}
