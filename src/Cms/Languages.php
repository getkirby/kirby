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
     * Creates a new language with the given props
     *
     * @param array $props
     * @return Language
     */
    public function create(array $props): Language
    {
        return Language::create($props);
    }

    /**
     * Returns the default language
     *
     * @return Language|null
     */
    public function default(): ?Language
    {
        if ($language = $this->findBy('isDefault', true)) {
            return $language;
        } else {
            return $this->first();
        }
    }

    /**
     * Deprecated version of static::default();
     *
     * @return Language|null
     */
    public function findDefault(): ?Language
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

            if (is_array($props) === true) {

                // inject the language code from the filename if it does not exist
                $props['code'] = $props['code'] ?? F::name($file);

                $language = new Language($props);
                $languages->data[$language->code()] = $language;
            }
        }

        return $languages;
    }
}
