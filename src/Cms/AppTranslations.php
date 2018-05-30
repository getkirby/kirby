<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

trait AppTranslations
{
    protected $translations;

    /**
     * Loads the fallback translation and
     * runs the I18n setup.
     *
     * @return void
     */
    protected function loadFallbackTranslation()
    {
        I18n::$locale   = 'en';
        I18n::$fallback = I18n::$translation = $this->translation(I18n::$locale)->data();
    }

    /**
     * Loads the user translation
     *
     * @param string $locale
     * @return void
     */
    protected function loadTranslation(string $locale)
    {
        if ($locale !== I18n::$locale && $translation = $this->translation($locale)) {
            I18n::$locale      = $locale;
            I18n::$translation = $translation->data();
        }
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
        if (is_a($this->translations, Translations::class) === true) {
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
        if (is_a($this->translations, Translations::class) === true) {
            return $this->translations;
        }

        return Translations::load($this->root('translations'));
    }
}
