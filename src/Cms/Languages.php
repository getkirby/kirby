<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;

/**
 * A collection of all defined site languages
 */
class Languages extends Collection
{

    /**
     * Returns all language codes as array
     *
     * @return array
     */
    public function codes(): array
    {
        return $this->keys();
    }

    /**
     * Returns the default language
     *
     * @return Language
     */
    public function default(): Language
    {
        return $this->findBy('isDefault', true) ?? $this->first();
    }

    /**
     * Deprecated version of static::default();
     *
     * @return Language
     */
    public function findDefault(): Language
    {
        return $this->default();
    }

    /**
     * Convert all defined languages to a collection
     *
     * @return self
     */
    public static function load(): self
    {
        $languages = new static;
        $files     = glob(App::instance()->root('languages') . '/*.php');

        foreach ($files as $file) {

            $props = include_once $file;

            // inject the language code from the filename if it does not exist
            $props['code'] = $props['code'] ?? F::name($file);

            if (is_array($props) === true) {
                $language = new Language($props);
                $languages->data[$language->code()] = $language;
            }

        }

        return $languages;
    }
}
