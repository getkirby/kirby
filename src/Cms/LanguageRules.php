<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
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
	 * @throws \Kirby\Exception\PermissionException If current user has not sufficient permissions
	 */
	public static function create(Language $language): void
	{
		static::validLanguageCode($language);
		static::validLanguageName($language);
		static::validLanguageVariables($language);

		$default = App::instance()->defaultLanguage();

		if ($language->isDefault() === true && $default !== null) {
			throw new LogicException(
				key: 'language.create.default',
				data: [
					'code' => $default->code(),
					'name' => $default->name()
				]
			);
		}

		if ($language->exists() === true) {
			throw new DuplicateException(
				key: 'language.duplicate',
				data: ['code' => $language->code()]
			);
		}

		if ($language->permissions()->can('create') !== true) {
			throw new PermissionException(
				key: 'language.create.permission'
			);
		}
	}

	/**
	 * Validates if the language can be deleted
	 *
	 * @throws \Kirby\Exception\LogicException If the language cannot be deleted
	 * @throws \Kirby\Exception\PermissionException If current user has not sufficient permissions
	 */
	public static function delete(Language $language): void
	{
		if ($language->permissions()->can('delete') !== true) {
			throw new PermissionException(
				key: 'language.delete.permission'
			);
		}
	}

	/**
	 * Validates if the language can be updated
	 */
	public static function update(
		Language $newLanguage,
		Language|null $oldLanguage = null
	): void {
		static::validLanguageCode($newLanguage);
		static::validLanguageName($newLanguage);
		static::validLanguageVariables($newLanguage, $oldLanguage);

		$kirby = App::instance();

		// if language was the default language and got demoted…
		if (
			$oldLanguage?->isDefault() === true &&
			$newLanguage->isDefault() === false &&
			$kirby->defaultLanguage()->code() === $oldLanguage?->code()
		) {
			// ensure another language has already been set as default
			throw new LogicException(
				message: 'Please select another language to be the primary language'
			);
		}

		if ($newLanguage->permissions()->can('update') !== true) {
			throw new PermissionException(
				key: 'language.update.permission'
			);
		}
	}

	/**
	 * Validates if the language code is formatted correctly
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language code is not valid
	 */
	public static function validLanguageCode(Language $language): void
	{
		if (Str::length($language->code()) < 2) {
			throw new InvalidArgumentException(
				key: 'language.code',
				data: [
					'code' => $language->code(),
					'name' => $language->name()
				]
			);
		}
	}

	/**
	 * Validates if the language name is formatted correctly
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language name is invalid
	 */
	public static function validLanguageName(Language $language): void
	{
		if (Str::length($language->name()) < 1) {
			throw new InvalidArgumentException(
				key: 'language.name',
				data: [
					'code' => $language->code(),
					'name' => $language->name()
				]
			);
		}
	}

	/**
	 * Validates the custom variables of a language. Only keys that are
	 * new or whose value changed are checked, so that a variable which
	 * is already stored never blocks an unrelated update.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If a variable key is invalid
	 * @since 5.6.0
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
				throw new InvalidArgumentException(
					key: 'language.variable.numeric'
				);
			}

			if ($key === '') {
				throw new InvalidArgumentException(
					key: 'language.variable.key'
				);
			}

			if (isset($core[$key]) === true) {
				throw new InvalidArgumentException(
					key: 'language.variable.core',
					data: ['key' => $key]
				);
			}
		}
	}
}
