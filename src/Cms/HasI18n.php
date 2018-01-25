<?php

namespace Kirby\Cms;

trait HasI18n
{

    protected static $i18nLanguage;

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
            return $input[static::i18n()] ?? $default;
        }

        if (is_string($input) === true) {
            return $input;
        }

        throw new Exception('Untranslatable input');
    }

    /**
     * Static setter and getter for the object's language
     * This is used in the i18n method to translate any
     * sort of property from string or array inputs according
     * to the given language.
     *
     * @param string $i18nLanguage
     * @return void
     */
    public static function i18nLanguage(string $i18nLanguage = null)
    {
        if ($i18nLanguage === null) {
            return static::$i18nLanguage ?? 'en';
        }

        return static::$i18nLanguage = $i18nLanguage;
    }

}
