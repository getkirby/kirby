<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

trait AppTranslations
{
    protected $translations;

    /**
     * Setup internationalization
     *
     * @return void
     */
    protected function i18n()
    {
        I18n::$load = function ($locale) {
            $data = [];

            if ($translation = $this->translation($locale)) {
                $data = $translation->data();
            }

            // inject translations from the current language
            if ($this->multilang() === true && $language = $this->languages()->find($locale)) {
                $data = array_merge($data, $language->translations());
            }

            return $data;
        };

        I18n::$locale = function () {
            if ($this->multilang() === true) {
                return $this->defaultLanguage()->code();
            } else {
                return 'en';
            }
        };

        I18n::$fallback = function () {
            if ($this->multilang() === true) {
                return $this->defaultLanguage()->code();
            } else {
                return 'en';
            }
        };

        I18n::$translations = [];
    }

    /**
     * Load and set the current language if it exists
     * Otherwise fall back to the default language
     *
     * @param string $languageCode
     * @return Language|null
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
     * @param string $translationCode
     * @return void
     */
    public function setCurrentTranslation(string $translationCode = null)
    {
        I18n::$locale = $translationCode ?? 'en';
    }

    /**
     * Set locale settings
     *
     * @param string|array $locale
     */
    public function setLocale($locale)
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
     * @return Translation|null
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
     * @return Translations
     */
    public function translations()
    {
        if (is_a($this->translations, 'Kirby\Cms\Translations') === true) {
            return $this->translations;
        }

        return Translations::load($this->root('translations'), $this->extensions['translations'] ?? []);
    }
}
