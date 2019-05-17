<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * AppTranslations
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait AppTranslations
{
    protected $translations;

    /**
     * Setup internationalization
     *
     * @return void
     */
    protected function i18n(): void
    {
        I18n::$load = function ($locale): array {
            $data = [];

            if ($translation = $this->translation($locale)) {
                $data = $translation->data();
            }

            // inject translations from the current language
            if ($this->multilang() === true && $language = $this->languages()->find($locale)) {
                $data = array_merge($data, $language->translations());

                // Add language slug rules to Str class
                Str::$language = $language->rules();
            }


            return $data;
        };

        I18n::$locale = function (): string {
            if ($this->multilang() === true) {
                return $this->defaultLanguage()->code();
            } else {
                return 'en';
            }
        };

        I18n::$fallback = function (): string {
            if ($this->multilang() === true) {
                return $this->defaultLanguage()->code();
            } else {
                return 'en';
            }
        };

        I18n::$translations = [];

        if (isset($this->options['slugs']) === true) {
            $file = $this->root('i18n:rules') . '/' . $this->options['slugs'] . '.json';

            if (F::exists($file) === true) {
                try {
                    $data = Data::read($file);
                } catch (\Exception $e) {
                    $data = [];
                }

                Str::$language = $data;
            }
        }
    }

    /**
     * Load and set the current language if it exists
     * Otherwise fall back to the default language
     *
     * @internal
     * @param string $languageCode
     * @return Kirby\Cms\Language|null
     */
    public function setCurrentLanguage(string $languageCode = null)
    {
        if ($this->multilang() === false) {
            $this->setLocale($this->option('locale', 'en_US.utf-8'));
            return $this->language = null;
        }

        if ($language = $this->language($languageCode)) {
            $this->language = $language;
        } else {
            $this->language = $this->defaultLanguage();
        }

        if ($this->language) {
            $this->setLocale($this->language->locale());
        }

        return $this->language;
    }

    /**
     * Set the current translation
     *
     * @internal
     * @param string $translationCode
     * @return void
     */
    public function setCurrentTranslation(string $translationCode = null): void
    {
        I18n::$locale = $translationCode ?? 'en';
    }

    /**
     * Set locale settings
     *
     * @internal
     * @param string|array $locale
     */
    public function setLocale($locale): void
    {
        if (is_array($locale) === true) {
            foreach ($locale as $key => $value) {
                setlocale($key, $value);
            }
        } else {
            setlocale(LC_ALL, $locale);
        }
    }

    /**
     * Load a specific translation by locale
     *
     * @param string|null $locale
     * @return Kirby\Cms\Translation|null
     */
    public function translation(string $locale = null)
    {
        $locale = $locale ?? I18n::locale();
        $locale = basename($locale);

        // prefer loading them from the translations collection
        if (is_a($this->translations, 'Kirby\Cms\Translations') === true) {
            if ($translation = $this->translations()->find($locale)) {
                return $translation;
            }
        }

        // get injected translation data from plugins etc.
        $inject = $this->extensions['translations'][$locale] ?? [];

        // load from disk instead
        return Translation::load($locale, $this->root('translations') . '/' . $locale . '.json', $inject);
    }

    /**
     * Returns all available translations
     *
     * @return Kirby\Cms\Translations
     */
    public function translations()
    {
        if (is_a($this->translations, 'Kirby\Cms\Translations') === true) {
            return $this->translations;
        }

        return Translations::load($this->root('translations'), $this->extensions['translations'] ?? []);
    }
}
