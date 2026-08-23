<?php

namespace Kirby\Cms;

/**
 * Validators for all language actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @deprecated 6.0.0 Use `$language->guards()` instead
 */
class LanguageRules
{
	public static function create(Language $language): void
	{
		$language->guards()->ensureExecutable('create');
	}

	public static function delete(Language $language): void
	{
		$language->guards()->ensureExecutable('delete');
	}

	public static function update(
		Language $newLanguage,
		Language|null $oldLanguage = null
	): void {
		$newLanguage->guards()->ensureExecutable('update', $oldLanguage);
	}

	public static function validLanguageCode(Language $language): void
	{
		$language->guards()->validators()->validateCode($language->code());
	}

	public static function validLanguageName(Language $language): void
	{
		$language->guards()->validators()->validateName($language->name());
	}
}
