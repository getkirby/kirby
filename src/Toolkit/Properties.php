<?php

namespace Kirby\Toolkit;

/**
 * Properties
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
trait Properties
{
    /**
     * Original or default values for properties
     * before sett methods have been called
     *
     * @var array
     */
    protected $propertyData = [];

    /**
     * Creates an instance with the same
     * initial properties.
     *
     * @param array $props
     * @return static
     */
    public function clone(array $props = [])
    {
        return new static(array_replace_recursive($this->propertyData, $props));
    }

    /**
     * Creates a clone and fetches
     * lazy-loaded getters to get a
     * full copy
     *
     * @return static
     */
    public function hardcopy()
    {
        $clone = $this->clone();
        foreach (array_keys($this->propertyData) as $prop) {
            $this->{$prop}();
        }
        return $clone;
    }

    /**
     * Sets the data for all named properties
     * by calling the dedicated prop setter method
     *
     * @param array $data
     * @param array $props
     * @return $this
     */
    protected function setProperties(array $data, array $props)
    {
        // loop through all the prop names
        // that have been passed to be se
        foreach ($props as $prop) {

            // store the data value for the property;
            // if none has been passed, use the property
            // default and `null` as fallbacks
            $this->propertyData[$prop] = $data[$prop] ?? $this->$prop ?? null;

            // call the setter method for the prop:
            // if a data value for the prop has been passed,
            // pass this value to the setter method
            if (isset($data[$prop]) === true) {
                $this->{'set' . $prop}($data[$prop]);
            } else {
                $this->{'set' . $prop}();
            }
        }

        return $this;
    }
}
