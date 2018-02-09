<?php

namespace Kirby\Cms;

use Exception;
use ReflectionClass;
use ReflectionProperty;

trait HasUnknownProperties
{

    /**
     * All unexpected properties
     *
     * @var array
     */
    protected $unknownProperties = [];

    /**
     * Magic method caller
     * to enable getting unexpected attributes
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        return $this->getUnknownProperty($method);
    }

    /**
     * Returns all unknown properties
     *
     * @return array
     */
    public function getUnknownProperties(): array
    {
        return $this->unknownProperties;
    }

    /**
     * Returns a single property value if
     * such a property exists
     *
     * @param string $key
     * @return mixed
     */
    public function getUnknownProperty(string $key, $default = null)
    {
        return $this->unknownProperties[$key] ?? $default;
    }

    /**
     * Checks if an unknown property exists
     *
     * @param string $key
     * @return boolean
     */
    protected function hasUnknownProperty(string $key): bool
    {
        return isset($this->unknownProperties[$key]) === true;
    }

    /**
     * @param array $unknownProperties
     * @return self
     */
    protected function setUnknownProperties(array $unknownProperties = [])
    {
        $class = new ReflectionClass($this);

        // start a clean props array
        $this->unknownProperties = [];

        // filter all properties that have been defined properly
        foreach ($unknownProperties as $key => $value) {
            try {
                $property = $class->getProperty($key);
                if ($property->isStatic() === true) {
                    throw new Exception('Static property');
                }
            } catch (Exception $e) {
                $this->unknownProperties[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Extended array conversion
     * Add the unexpected properties
     * to the output array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array_merge($this->getUnknownProperties(), parent::toArray());

        // make sure everything is still neat and tidy
        ksort($array);

        return $array;
    }

}
