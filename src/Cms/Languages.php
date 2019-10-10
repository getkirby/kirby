<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Toolkit\F;

/**
 * A collection of all defined site languages
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Languages extends Collection
{
    /**
     * Creates a new collection with the given language objects
     *
     * @param array $objects
     * @param object $parent
     */
    public function __construct($objects = [], $parent = null)
    {
        $defaults = array_filter($objects, function ($language) {
            return $language->isDefault() === true;
        });

        if (count($defaults) > 1) {
            throw new DuplicateException('You cannot have multiple default languages. Please check your language config files.');
        }

        parent::__construct($objects, $parent);
    }

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
     * @internal
     * @param array $props
     * @return \Kirby\Cms\Language
     */
    public function create(array $props)
    {
        return Language::create($props);
    }

    /**
     * Returns the default language
     *
     * @return \Kirby\Cms\Language|null
     */
    public function default()
    {
        if ($language = $this->findBy('isDefault', true)) {
            return $language;
        } else {
            return $this->first();
        }
    }

    /**
     * @deprecated 3.0.0  Use `Languages::default()` instead
     * @return \Kirby\Cms\Language|null
     */
    public function findDefault()
    {
        deprecated('$languages->findDefault() is deprecated, use $languages->default() instead. $languages->findDefault() will be removed in Kirby 3.5.0.');

        return $this->default();
    }

    /**
     * Convert all defined languages to a collection
     *
     * @internal
     * @return self
     */
    public static function load()
    {
        $languages = [];
        $files     = glob(App::instance()->root('languages') . '/*.php');

        foreach ($files as $file) {
            $props = include $file;

            if (is_array($props) === true) {
                // inject the language code from the filename if it does not exist
                $props['code'] = $props['code'] ?? F::name($file);

                $languages[] = new Language($props);
            }
        }

        return new static($languages);
    }
}
