<?php

namespace Kirby\Util;

use Exception;
use ReflectionClass;
use ReflectionMethod;

trait Properties
{

    protected $propertyData = [];

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

    protected function setOptionalProperties($props, $optional)
    {
        foreach ($optional as $propertyName) {
            if (isset($props[$propertyName]) === true) {
                $this->setProperty($propertyName, $props[$propertyName]);
            }
        }
    }

    protected function setProperties($props)
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

    protected function setRequiredProperties($props, $required)
    {
        foreach ($required as $propertyName) {
            $this->setProperty($propertyName, $props[$propertyName] ?? null, true);
        }
    }

}
