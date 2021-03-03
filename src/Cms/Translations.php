<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * A collection of all available Translations.
 * Provides a factory method to convert an array
 * to a collection of Translation objects and load
 * method to load all translations from disk
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Translations extends Collection
{
    /**
     * @param string $code
     * @return void
     */
    public function start(string $code): void
    {
        F::move($this->parent->contentFile('', true), $this->parent->contentFile($code, true));
    }

    /**
     * @param string $code
     * @return void
     */
    public function stop(string $code): void
    {
        F::move($this->parent->contentFile($code, true), $this->parent->contentFile('', true));
    }

    /**
     * @param array $translations
     * @return static
     */
    public static function factory(array $translations)
    {
        $collection = new static();

        foreach ($translations as $code => $props) {
            $translation = new Translation($code, $props);
            $collection->data[$translation->code()] = $translation;
        }

        return $collection;
    }

    /**
     * @param string $root
     * @param array $inject
     * @return static
     */
    public static function load(string $root, array $inject = [])
    {
        $collection = new static();

        foreach (Dir::read($root) as $filename) {
            if (F::extension($filename) !== 'json') {
                continue;
            }

            $locale      = F::name($filename);
            $translation = Translation::load($locale, $root . '/' . $filename, $inject[$locale] ?? []);

            $collection->data[$locale] = $translation;
        }

        return $collection;
    }
}
