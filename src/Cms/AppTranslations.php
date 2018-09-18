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
                $data = $translation->data();

                // inject translations from the current language
                if ($this->multilang() === true && $language = $this->languages()->find($locale)) {
                    $data = array_merge($data, $language->translations());
                }

                return $data;
            }

            return $translations[$locale] ?? [];
        };

        I18n::$locale       = 'en';
        I18n::$fallback     = 'en';
        I18n::$translations = [];
    }

    /**
     * Apply the current language
     *
     * @param Language $language
     * @return void
     */
    public function localize(Language $language = null)
    {
        $this->language = $language;

        if ($language !== null) {
            I18n::$locale = $language->code();
            setlocale(LC_ALL, $language->locale());
        } elseif ($locale = ($this->options['locale'] ?? null)) {
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
        $locale = $locale ?? I18n::$locale;
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
