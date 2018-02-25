<?php

namespace Kirby\Util;

use Exception;

trait I18n
{

    /**
     * @var string
     */
    protected $locale;

    /**
     * @return string
     */
    protected function defaultLocale(): string
    {
        return 'en_US';
    }

    /**
     * Translates the given input according to
     * the set $i8nLanguage, if the input is defined
     * as array with values for each language:
     *
     * ```
     * $this->i18n(['en' => 'Hey', 'de' => 'Ho']);
     * ```
     *
     * @param string|array $input
     * @param string $default
     * @return string|null
     */
    protected function i18n($input, $default = null)
    {
        if ($input === null) {
            return null;
        }

        if (is_array($input) === true) {
            return $input[$this->locale()] ?? $input[$this->defaultLocale()] ?? $default;
        }

        if (is_string($input) === true) {
            return $input;
        }

        throw new Exception('Untranslatable input');
    }

    public function locale()
    {
        return $this->locale;
    }

    protected function setLocale(string $locale = 'en'): self
    {
        $this->locale = empty($locale) === true ? $this->defaultLocale() : $locale;
        return $this;
    }

}
