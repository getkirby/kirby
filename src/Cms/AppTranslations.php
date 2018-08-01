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
            if ($translation = $this->translation($locale)) {
                return $translation->data();
            }

            return $translations[$locale] ?? [];
        };

        I18n::$locale       = 'en';
        I18n::$fallback     = 'en';
        I18n::$translations = [];
    }

    /**
     * Create your own set of translations
     *
     * @param array $translations
     * @return self
     */
    protected function setTranslations(array $translations = null): self
    {
        if ($translations !== null) {
            $this->translations = Translations::factory($translations);
        }

        return $this;
    }

    /**
     * Load a specific translation by locale
     *
     * @param string|null $locale
     * @return Translation|null
     */
    public function translation(string $locale = null)
    {
        $locale = $locale ?? I18n::$locale;
        $locale = basename($locale);

        // prefer loading them from the translations collection
        if (is_a($this->translations, 'Kirby\Cms\Translations') === true) {
            if ($translation = $this->translations()->find($locale)) {
                return $translation;
            }
        }

        // load from disk instead
        return Translation::load($locale, $this->root('translations') . '/' . $locale . '.json');
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

        return Translations::load($this->root('translations'));
    }
}
