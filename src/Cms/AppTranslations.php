<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Locale;
use Kirby\Toolkit\Str;

/**
 * AppTranslations
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait AppTranslations
{
	protected Translations|null $translations = null;

	/**
	 * Setup internationalization
	 */
	protected function i18n(): void
	{
		I18n::$load = function ($locale): array {
			$data = $this->translation($locale)?->data() ?? [];

			// inject translations from the current language
			if (
				$this->multilang() === true &&
				$language = $this->languages()->find($locale)
			) {
				$data = array_merge($data, $language->translations());
			}


			return $data;
		};

		// the actual locale is set using $app->setCurrentTranslation()
		I18n::$locale = function (): string {
			if ($this->multilang() === true) {
				return $this->defaultLanguage()->code();
			}

			return 'en';
		};

		I18n::$fallback = function (): array {
			if ($this->multilang() === true) {
				// first try to fall back to the configured default language
				$defaultCode = $this->defaultLanguage()->code();
				$fallback = [$defaultCode];

				// if the default language is specified with a country code
				// (e.g. `en-us`), also try with just the language code
				if (preg_match('/^([a-z]{2})-[a-z]+$/i', $defaultCode, $matches) === 1) {
					$fallback[] = $matches[1];
				}

				// fall back to the complete English translation
				// as a last resort
				$fallback[] = 'en';

				return $fallback;
			}

			return ['en'];
		};

		I18n::$translations = [];

		// add slug rules based on config option
		if ($slugs = $this->option('slugs')) {
			// two ways that the option can be defined:
			// "slugs" => "de" or "slugs" => ["language" => "de"]
			if ($slugs = $slugs['language'] ?? $slugs ?? null) {
				Str::$language = Language::loadRules($slugs);
			}
		}
	}

	/**
	 * Returns the language code that will be used
	 * for the Panel if no user is logged in or if
	 * no language is configured for the user
	 */
	public function panelLanguage(): string
	{
		if ($this->multilang() === true) {
			$defaultCode = $this->defaultLanguage()->code();

			// extract the language code from a language that
			// contains the country code (e.g. `en-us`)
			if (preg_match('/^([a-z]{2})-[a-z]+$/i', $defaultCode, $matches) === 1) {
				$defaultCode = $matches[1];
			}
		} else {
			$defaultCode = 'en';
		}

		return $this->option('panel.language', $defaultCode);
	}

	/**
	 * Load and set the current language if it exists
	 * Otherwise fall back to the default language
	 *
	 * @internal
	 */
	public function setCurrentLanguage(
		string $languageCode = null
	): Language|null {
		if ($this->multilang() === false) {
			Locale::set($this->option('locale', 'en_US.utf-8'));
			return $this->language = null;
		}

		$this->language   = $this->language($languageCode);
		$this->language ??= $this->defaultLanguage();

		if ($this->language) {
			Locale::set($this->language->locale());
		}

		// add language slug rules to Str class
		Str::$language = $this->language->rules();

		return $this->language;
	}

	/**
	 * Set the current translation
	 *
	 * @internal
	 */
	public function setCurrentTranslation(string $translationCode = null): void
	{
		I18n::$locale = $translationCode ?? 'en';
	}

	/**
	 * Load a specific translation by locale
	 *
	 * @param string|null $locale Locale name or `null` for the current locale
	 */
	public function translation(string|null $locale = null): Translation
	{
		$locale = $locale ?? I18n::locale();
		$locale = basename($locale);

		// prefer loading them from the translations collection
		if ($this->translations instanceof Translations) {
			if ($translation = $this->translations()->find($locale)) {
				return $translation;
			}
		}

		// get injected translation data from plugins etc.
		$inject = $this->extensions['translations'][$locale] ?? [];

		// inject current language translations
		if ($language = $this->language($locale)) {
			$inject = array_merge($inject, $language->translations());
		}

		// load from disk instead
		return Translation::load($locale, $this->root('i18n:translations') . '/' . $locale . '.json', $inject);
	}

	/**
	 * Returns all available translations
	 */
	public function translations(): Translations
	{
		if ($this->translations instanceof Translations) {
			return $this->translations;
		}

		$translations = $this->extensions['translations'] ?? [];

		// injects languages translations
		if ($languages = $this->languages()) {
			foreach ($languages as $language) {
				$languageCode         = $language->code();
				$languageTranslations = $language->translations();

				// merges language translations with extensions translations
				if (empty($languageTranslations) === false) {
					$translations[$languageCode] = array_merge(
						$translations[$languageCode] ?? [],
						$languageTranslations
					);
				}
			}
		}

		return $this->translations = Translations::load($this->root('i18n:translations'), $translations);
	}
}
