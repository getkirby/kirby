<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * A collection of all available Translations.
 * Provides a factory method to convert an array
 * to a collection of Translation objects and load
 * method to load all translations from disk
 */
class Translations extends Collection
{
    protected static $accept = Translation::class;

    public static function factory(array $translations)
    {
        $collection = new static;

        foreach ($translations as $code => $props) {
            $translation = new Translation($code, $props);
            $collection->data[$translation->code()] = $translation;
        }

        return $collection;
    }

    public static function load(string $root)
    {
        $collection = new static;

        foreach (Dir::read($root) as $filename) {
            if (F::extension($filename) !== 'json') {
                continue;
            }

            $translation = Translation::load($code = F::name($filename), $root . '/' . $filename);
            $collection->data[$code] = $translation;
        }

        return $collection;
    }
}
