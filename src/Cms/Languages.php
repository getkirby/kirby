<?php

namespace Kirby\Cms;

use Kirby\Content\VersionId;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Filesystem\F;

/**
 * A collection of all defined site languages
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Collection<\Kirby\Cms\Language>
 */
class Languages extends Collection
{
	/**
	 * All registered languages methods
	 */
	public static array $methods = [];

	/**
	 * Creates a new collection with the given language objects
	 *
	 * @param null $parent
	 * @throws \Kirby\Exception\DuplicateException
	 */
	public function __construct(
		array $objects = [],
		$parent = null
	) {
		$defaults = array_filter(
			$objects,
			fn ($language) => $language->isDefault() === true
		);

		if (count($defaults) > 1) {
			throw new DuplicateException(
				message: 'You cannot have multiple default languages. Please check your language config files.'
			);
		}

		parent::__construct($objects, null);
	}

	/**
	 * Returns all language codes as array
	 */
	public function codes(): array
	{
		return App::instance()->multilang() ? $this->keys() : ['default'];
	}

	/**
	 * Creates a new language with the given props
	 * @internal
	 */
	public function create(array $props): Language
	{
		return Language::create($props);
	}

	/**
	 * Returns the default language
	 */
	public function default(): Language|null
	{
		return $this->findBy('isDefault', true) ?? $this->first();
	}

	/**
	 * Provides a collection of installed languages, even
	 * if in single-language mode. In single-language mode
	 * `Language::single()` is used to create the default language
	 *
	 * @internal
	 */
	public static function ensure(): static
	{
		$kirby = App::instance();

		if ($kirby->multilang() === true) {
			return $kirby->languages();
		}

		return new static([Language::single()]);
	}

	/**
	 * Convert all defined languages to a collection
	 * @internal
	 */
	public static function load(): static
	{
		$languages = [];
		$files     = glob(App::instance()->root('languages') . '/*.php');

		foreach ($files as $file) {
			$props = F::load($file, allowOutput: false);

			if (is_array($props) === true) {
				// inject the language code from the filename
				// if it does not exist
				$props['code'] ??= F::name($file);

				$languages[] = new Language($props);
			}
		}

		return new static($languages);
	}

	/**
	 * Converts a single language installation to a multi language installation
	 * by moving all content versions of the default language
	 * @internal
	 */
	public static function migrateToMultiLang(Language $defaultLanguage): void
	{
		$kirby          = App::instance();
		$languages      = $kirby->languages();
		$singleLanguage = Language::single();

		if ($languages->count() > 0) {
			throw new Exception('There are already multiple languages');
		}

		if ($defaultLanguage->isDefault() === false) {
			throw new Exception('The given language is not the default language');
		}

		foreach ($kirby->models() as $model) {
			foreach (VersionId::all() as $versionId) {
				$version = $model->version($versionId);

				if ($version->exists($singleLanguage) === true) {
					$version->move(
						fromLanguage: $singleLanguage,
						toLanguage: $defaultLanguage,
					);
				}
			}
		}
	}

	/**
	 * Removes all content versions of a language from all models
	 * @internal
	 */
	public static function migrateToSingleLang(Language $lastLanguage): void
	{
		$kirby          = App::instance();
		$languages      = $kirby->languages();
		$singleLanguage = Language::single();

		if ($languages->count() === 0) {
			throw new Exception('There are no languages defined');
		}

		if ($lastLanguage->isLast() === false) {
			throw new Exception('The language is not the last one');
		}

		foreach ($kirby->models() as $model) {
			foreach (VersionId::all() as $versionId) {
				$version = $model->version($versionId);

				if ($version->exists($singleLanguage) === true) {
					$version->move(
						fromLanguage: $lastLanguage,
						toLanguage: $singleLanguage,
					);
				}
			}
		}
	}
}
