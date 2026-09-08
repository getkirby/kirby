<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Validators for all language actions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageRules
{
	/**
	 * Validates if the language can be created
	 *
	 * @throws \Kirby\Exception\DuplicateException If the language already exists
	 */
	public static function create(Language $language): bool
	{
		static::validLanguageCode($language);
		static::validLanguageName($language);
		static::validLanguageVariables($language);

		if ($language->exists() === true) {
			throw new DuplicateException([
				'key'  => 'language.duplicate',
				'data' => [
					'code' => $language->code()
				]
			]);
		}

		return true;
	}

	/**
	 * Validates if the language can be updated
	 */
	public static function update(
		Language $language,
		Language|null $oldLanguage = null
	): void {
		static::validLanguageCode($language);
		static::validLanguageName($language);
		static::validLanguageVariables($language, $oldLanguage);
	}

	/**
	 * Validates if the language code is formatted correctly
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language code is not valid
	 */
	public static function validLanguageCode(Language $language): bool
	{
		if (Str::length($language->code()) < 2) {
			throw new InvalidArgumentException([
				'key'  => 'language.code',
				'data' => [
					'code' => $language->code(),
					'name' => $language->name()
				]
			]);
		}

		return true;
	}

	/**
	 * Validates if the language name is formatted correctly
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language name is invalid
	 */
	public static function validLanguageName(Language $language): bool
	{
		if (Str::length($language->name()) < 1) {
			throw new InvalidArgumentException([
				'key'  => 'language.name',
				'data' => [
					'code' => $language->code(),
					'name' => $language->name()
				]
			]);
		}

		return true;
	}

	/**
	 * Validates the custom variables of a language. Only keys that are
	 * new or whose value changed are checked, so that a variable which
	 * is already stored never blocks an unrelated update.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If a variable key is invalid
	 * @since 4.9.6
	 */
	public static function validLanguageVariables(
		Language $newLanguage,
		Language|null $oldLanguage = null
	): void {
		$old     = $oldLanguage?->translations() ?? [];
		$changed = array_filter(
			$newLanguage->translations(),
			fn ($value, $key) => ($old[$key] ?? null) !== $value,
			ARRAY_FILTER_USE_BOTH
		);

		if ($changed === []) {
			return;
		}

		$core = App::instance()->coreI18nStrings($newLanguage->code());

		foreach (array_keys($changed) as $key) {
			if (is_numeric($key) === true) {
				throw new InvalidArgumentException([
					'key' => 'language.variable.numeric'
				]);
			}

			if ($key === '') {
				throw new InvalidArgumentException([
					'key' => 'language.variable.key'
				]);
			}

			if (isset($core[$key]) === true) {
				throw new InvalidArgumentException([
					'key'  => 'language.variable.core',
					'data' => [
						'key' => $key
					]
				]);
			}
		}
	}
}
