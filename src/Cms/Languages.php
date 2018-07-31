<?php

namespace Kirby\Cms;

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
    public function findDefault(): Language
    {
        return $this->findBy('isDefault', true);
    }

    /**
     * Convert all defined languages to a collection
     *
     * @return self
     */
    public static function load(): self
    {
        $languages = new static;

        foreach (App::instance()->option('languages', []) as $props) {
            $language = new Language($props);
            $languages->data[$language->code()] = $language;
        }

        return $languages;
    }
}
