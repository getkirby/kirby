<?php

namespace Kirby\Toolkit;

use Exception;
use ReflectionClass;
use ReflectionMethod;

trait Properties
{
    protected $propertyData = [];

    /**
     * Creates an instance with the same
     * initial properties.
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = [])
    {
        return new static(array_replace_recursive($this->propertyData, $props));
    }

    /**
     * Creates a clone and fetches all
     * lazy-loaded getters to get a full copy
     *
     * @return self
     */
    public function hardcopy()
    {
        $clone = $this->clone();
        $clone->propertiesToArray();
        return $clone;
    }

    protected function isRequiredProperty(string $name): bool
    {
        $method = new ReflectionMethod($this, 'set' . $name);
        return $method->getNumberOfRequiredParameters() > 0;
    }

    protected function propertiesToArray()
    {
        $array = [];

        foreach (get_object_vars($this) as $name => $default) {
            if ($name === 'propertyData') {
                continue;
            }

            if (method_exists($this, 'convert' . $name . 'ToArray') === true) {
                $array[$name] = $this->{'convert' . $name . 'ToArray'}();
                continue;
            }

            if (method_exists($this, $name) === true) {
                $method = new ReflectionMethod($this, $name);

                if ($method->isPublic() === true) {
                    $value = $this->$name();

                    if (is_object($value) === false) {
                        $array[$name] = $value;
                    }
                }
            }
        }

        ksort($array);

        return $array;
    }

    protected function setOptionalProperties(array $props, array $optional)
    {
        $this->propertyData = array_merge($this->propertyData, $props);

        foreach ($optional as $propertyName) {
            if (isset($props[$propertyName]) === true) {
                $this->{'set' . $propertyName}($props[$propertyName]);
            } else {
                $this->{'set' . $propertyName}();
            }
        }
    }

    protected function setProperties($props, array $keys = null)
    {
        foreach (get_object_vars($this) as $name => $default) {
            if ($name === 'propertyData') {
                continue;
            }

            $this->setProperty($name, $props[$name] ?? $default);
        }

        return $this;
    }

    protected function setProperty($name, $value, $required = null)
    {
        // use a setter if it exists
        if (method_exists($this, 'set' . $name) === false) {
            return $this;
        }

        // fetch the default value from the property
        $value = $value ?? $this->$name ?? null;

        // store all original properties, to be able to clone them later
        $this->propertyData[$name] = $value;

        // handle empty values
        if ($value === null) {

            // replace null with a default value, if a default handler exists
            if (method_exists($this, 'default' . $name) === true) {
                $value = $this->{'default' . $name}();
            }

            // check for required properties
            if ($value === null && ($required ?? $this->isRequiredProperty($name)) === true) {
                throw new Exception(sprintf('The property "%s" is required', $name));
            }
        }

        // call the setter with the final value
        return $this->{'set' . $name}($value);
    }

    protected function setRequiredProperties(array $props, array $required)
    {
        foreach ($required as $propertyName) {
            if (isset($props[$propertyName]) !== true) {
                throw new Exception(sprintf('The property "%s" is required', $propertyName));
            }

            $this->{'set' . $propertyName}($props[$propertyName]);
        }
    }
}
