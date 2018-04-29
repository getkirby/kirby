<?php

namespace Kirby\Form;

use Exception;
use Kirby\Data\Handler\Yaml;
use Kirby\Util\Str;

trait HasValue
{

    protected $value = null;
    protected $undefined = true;

    protected function emptyValues(): array
    {
        return [null, '', []];
    }

    public function error()
    {
        try {
            $this->methods()->validate();
            return false;
        } catch (Exception $e) {
            return [
                'name'    => $this->name(),
                'label'   => $this->label(),
                'message' => $e->getMessage(),
                'type'    => 'field',
                'cause'   => $e->getKey()
            ];
        }
    }

    public function isEmpty(): bool
    {
        $args  = func_get_args();
        $value = count($args) > 0 ? $args[0] : $this->value();

        return in_array($value, $this->emptyValues(), true) === true;
    }

    /**
     * Checks if the field value is set
     * or undefined
     *
     * @return boolean
     */
    public function isUndefined(): bool
    {
        return $this->undefined;
    }

    /**
     * Checks if the current field value is valid
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        try {
            $this->methods()->validate();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function setValue($value)
    {
        $this->value = $value;

        if ($this->value !== null) {
            $this->undefined = false;
        }

        // set the default if the value is empty
        $value = $this->isUndefined() === true ? $this->default() : $this->value;

        // convert the given value to a data value
        $this->value = $this->valueToApi();
    }

    public function value()
    {
        return $this->value;
    }

    public function valueToApi()
    {
        return $this->methods()->toApi($this->value());
    }

    public function valueToString(): string
    {
        return (string)$this->methods()->toString($this->value());
    }

    protected function valueFromList($value, $separator = ','): array
    {
        if (is_array($value) === true) {
            return $value;
        }

        return Str::split($value, $separator);
    }

    protected function valueFromYaml($value): array
    {
        if (is_array($value) === true) {
            return $value;
        }

        if ($value === null) {
            return [];
        }

        return Yaml::decode($value);
    }

    protected function valueToList($value, $separator = ', '): string
    {
        return implode($separator, $value);
    }

    protected function valueToYaml($value): string
    {
        return Yaml::encode($value);
    }

}
