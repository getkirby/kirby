<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
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
	protected array $i18nStrings = [];
	protected Translations|null $translations = null;

	/**
	 * Returns the translation strings that Kirby itself defines for
	 * the given code. Every shipped translation is generated from the
	 * English one, so that is always the complete set, plus whatever
	 * plugins register for English and for the code itself.
	 * @since 5.6.0
	 */
	public function coreI18nStrings(string $code): array
	{
		return $this->i18nStrings[$code] ??= [
			...Translation::load(
				'en',
				$this->root('i18n:translations') . '/en.json',
				$this->extensions['translations']['en'] ?? []
			)->data(),
			...$this->extensions['translations'][$code] ?? []
		];
	}

	/**
	 * Returns the translation strings that the custom variables of a
	 * language contribute. A variable must never shadow one of Kirby's
	 * own strings, as the Panel renders some of them as HTML.
	 */
	protected function customI18nStrings(Language $language): array
	{
		if (($strings = $language->translations()) === []) {
			return [];
		}

		return array_diff_key(
			$strings,
			$this->coreI18nStrings($language->code())
		);
	}

	/**
	 * Setup internationalization
	 */
	protected function i18n(): void
	{
		I18n::$load = fn ($locale): array =>
			$this->translation($locale)->data();

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
				$fallback    = [$defaultCode];

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
			Str::$language = Language::loadRules($slugs['language'] ?? $slugs);
		}
	}

	/**
	 * Returns the language code that will be used
	 * for the Panel if no user is logged in or if
	 * no language is configured for the user
	 */
	public function panelLanguage(): string
	{
		$translation = $this->request()->get('translation');

		if ($translation !== null && $this->translations()->find($translation)) {
			return $translation;
		}

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
	 * Set the current translation
	 */
	public function setCurrentTranslation(string|null $translationCode = null): void
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
		$locale ??= I18n::locale();
		$locale   = basename($locale);

		// prefer loading them from the translations collection
		if ($this->translations instanceof Translations) {
			if ($translation = $this->translations()->find($locale)) {
				return $translation;
			}
		}

		// get injected translation data from plugins etc.
		$inject = $this->extensions['translations'][$locale] ?? [];

		// inject the strings of the current language's custom variables
		if ($language = $this->language($locale)) {
			$inject = [...$inject, ...$this->customI18nStrings($language)];
		}

		// load from disk instead
		return Translation::load(
			$locale,
			$this->root('i18n:translations') . '/' . $locale . '.json',
			$inject
		);
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
				$code    = $language->code();
				$strings = $this->customI18nStrings($language);

				// merges the custom variables with the extension translations
				if ($strings !== []) {
					$translations[$code] = [
						...$translations[$code] ?? [],
						...$strings
					];
				}
			}
		}

		return $this->translations = Translations::load(
			$this->root('i18n:translations'),
			$translations
		);
	}
}
