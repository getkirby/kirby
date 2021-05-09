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
        $clone->toArray();
        return $clone;
    }

    protected function setProperties(array $data, array $names)
    {
        foreach ($names as $name) {
            $this->propertyData[$name] = $data[$name] ?? $this->$name ?? null;

            if (isset($data[$name]) === true) {
                $this->{'set' . $name}($this->propertyData[$name]);
            } else {
                $this->{'set' . $name}();
            }
        }
    }
}
