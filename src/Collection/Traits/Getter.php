<?php

namespace Kirby\Collection\Traits;

trait Getter
{

    /**
     * Low-level getter for items
     *
     * @param  mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        $lowerkeys = array_change_key_case($this->data, CASE_LOWER);
        return $lowerkeys[strtolower($key)] ?? null;
    }

    /**
     * Getter
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->__get($key) ?? $default;
    }

    /**
     * Magic getter function
     *
     * @param  string $key
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call(string $key, $arguments)
    {
        return $this->__get($key);
    }

    public function getAttribute($item, string $attribute)
    {
        return $this->{'getAttributeFrom' . gettype($item)}($item, $attribute);
    }

    protected function getAttributeFromArray(array $array, string $attribute)
    {
        return $array[$attribute] ?? null;
    }

    protected function getAttributeFromObject($object, string $attribute)
    {
        return $object->{$attribute}();
    }

}
