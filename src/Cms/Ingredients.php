<?php

namespace Kirby\Cms;

/**
 * The Ingredients class can be
 * used to define simple attributes
 * and their getters to put options/urls/roots
 * etc. into a nice object
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
abstract class Ingredients
{
    /**
     * Sets all defined class properties
     * which can also be found in the values array
     *
     * @param array $values
     */
    public function __construct(array $values = null)
    {
        if ($values !== null) {
            foreach (get_object_vars($this) as $key => $value) {
                $this->$key = $values[$key] ?? null;
            }
        }
    }

    /**
     * Exports all defined class properties
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach (get_object_vars($this) as $key => $value) {
            $array[$key] = $this->$key();
        }

        ksort($array);
        return $array;
    }
}
