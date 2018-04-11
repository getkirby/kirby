<?php

namespace Kirby\Cms;

trait AppTranslations
{

    /**
     * Returns the current locale
     *
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * Returns all available translations
     *
     * @return Translations
     */
    public function translations()
    {
        return $this->component('translations');
    }

    /**
     * Sets the current locale
     *
     * @param string $locale
     * @return self
     */
    protected function setLocale(string $locale = 'en'): self
    {
        $this->locale = empty($locale) === true ? $this->defaultLocale() : $locale;
        return $this;
    }

    /**
     * Returns translate string for key from locales file
     *
     * @param   string       $key
     * @param   string|null  $fallback
     * @param   string|null  $locale
     * @return  string|null
     */
    public function translate(string $key, string $fallback = null, string $locale = null)
    {
        // TODO: define at a better place
        $defaultLocale = 'en_US';

        // TODO: handle short locales
        if ($locale === null) {
            if ($user = $this->user()) {
                $locale = $user->language() ?? $defaultLocale;
            } else {
                $locale = $defaultLocale;
            }
        }

        $translations = $this->translations();

        // if current language file has translation, return it
        if ($translation = $translations->get($locale)->get($key)) {
            return $translation;
        }

        // otherwise use default language file or
        // return fallback string if no translation at all exists
        return $translations->get($defaultLocale)->get($key, $fallback);
    }

}
