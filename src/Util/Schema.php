<?php

namespace Kirby\Util;

use Closure;
use Exception;

/**
 * The Schema class can be used
 * to create reliable prop/attribute
 * schemas including type checks and validation
 * for any kind of purpose.
 *
 * @package   Kirby Util
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Schema
{

    /**
     * Holds the Schema definition
     *
     * @var array
     */
    protected $schema;

    /**
     * Creates a new Schema object
     *
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Improved output for var_dump
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Validates the value and returns true
     * or false. It's basically the same as
     * Schema::validate, but doesn't throw
     * Exceptions on errors.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function accepts(string $key, $value): bool
    {
        try {
            return $this->validate($key, $value);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Extends the schema definition
     *
     * @param array $schema
     * @return self
     */
    public function extend(array $schema): self
    {
        $this->schema = array_replace_recursive($this->schema, $schema);
        return $this;
    }

    /**
     * Returns the schema definition for a
     * specific field
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key)
    {
        return $this->schema[$key] ?? null;
    }

    /**
     * Checks if the Schema has a definition
     * for the given key
     *
     * @return boolean
     */
    public function has(string $key)
    {
        return isset($this->schema[$key]) === true;
    }

    /**
     * Returns a list of all keys in the schema
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->schema);
    }

    /**
     * Plucks all fields defined in the schema
     * from the given $data array and returns
     * the result. Every field that is not included
     * in the $data array, but defined in the schema
     * will be set to null.
     *
     * @param array $data
     * @return array
     */
    public function pluck(array $data): array
    {
        $result = [];

        foreach ($this->schema as $key => $definition) {
            $result[$key] = $data[$key] ?? null;
        }

        return $result;
    }

    /**
     * Removes definitions from the schema
     * by key
     *
     * @return self
     */
    public function remove(...$keys): self
    {
        foreach ($keys as $key) {
            if (is_array($key) === true) {
                foreach ($key as $k) {
                    unset($this->schema[$k]);
                }
            } else {
                unset($this->schema[$key]);
            }
        }
        return $this;
    }

    /**
     * Returns the Schema definition
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->schema;
    }

    /**
     * Throws an Exception with info about the
     * failed prop type validation and the current class
     *
     * @param string $key
     * @param string $value
     * @return Exception
     */
    protected function typeError(string $key, $value): Exception
    {
        if (isset($this->schema[$key]) === false) {
            return true;
        }

        $expectedType = $this->schema[$key]['type'] ?? null;
        $valueType    = gettype($value);

        if ($expectedType === null) {
            return true;
        }

        if ($valueType === 'object') {
            $valueType = get_class($value);
        }

        return new Exception(sprintf('The "%s" property must be of type "%s" not "%s"', $key, $expectedType, $valueType));
    }

    /**
     * Validates a given value by the rules
     * set in the schema. It validates by
     * the given type and an optional custom
     * validate rule. This will throw Exceptions
     * on errors. Use the Schema::accepts method
     * to get true or false as results.
     *
     * @param string|array $key
     * @param mixed $value
     * @return bool
     */
    public function validate($key, $value = null): bool
    {
        if (is_array($key) === true) {

            // pluck an array with all fields
            // in the schema, to also make sure
            // we validate missing required fields
            // correctly.
            $data = $this->pluck($key);

            foreach ($data as $key => $value) {
                $this->validate($key, $value);
            }

            return true;
        }

        // get the schema definition for this field
        $schema = $this->schema[$key] ?? [];

        // skip fields without schema
        if (empty($schema) === true) {
            return true;
        }

        // check required fields
        if ($this->validateRequired($value, $schema['required'] ?? false) === false) {
            throw new Exception(sprintf('The "%s" property is required and must not be null', $key));
        }

        // skip validation if the field is empty
        if ($value === null) {
            return true;
        }

        if ($this->validateType($value, $schema['type'] ?? null) === false) {
            throw $this->typeError($key, $value);
        }

        if ($this->validateCustom($value, $schema['validate'] ?? null) === false) {
            throw new Exception(sprintf('Validation for the "%s" property failed', $key));
        }

        return true;
    }

    /**
     * Custom function validation used in Schema::validate
     *
     * @param mixed $value
     * @param Closure $function
     * @return bool
     */
    protected function validateCustom($value, Closure $function = null): bool
    {
        if (is_a($function, Closure::class) === false) {
            return true;
        }

        return $function($value);
    }

    /**
     * Used in Schema::validate to validate numeric values
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateNumber($value): bool
    {
        return is_string($value) === false && is_numeric($value) === true;
    }

    /**
     * Used in Schema::validate to validate objects
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateObject($value): bool
    {
        return is_object($value) === true;
    }

    /**
     * Validates required values
     *
     * @param mixed $value
     * @param bool $required
     * @return bool
     */
    protected function validateRequired($value, bool $required): bool
    {
        return $required === true && $value === null ? false : true;
    }

    /**
     * Used in Schema::validate to validate scalar values
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateScalar($value): bool
    {
        return is_scalar($value) === true;
    }

    /**
     * General type validation used in Schema::validate
     *
     * @param mixed $value
     * @param string $expectedType
     * @return bool
     */
    protected function validateType($value, string $expectedType = null): bool
    {
        // validation passes if no type is set
        if ($expectedType === null) {
            return true;
        }

        // get the type of the given value
        $valueType = gettype($value);

        // further validation is needed
        if ($valueType === $expectedType) {
            return true;
        }

        switch ($expectedType) {
            case 'number':
            case 'object':
            case 'scalar':
                return $this->{'validate' . $expectedType}($value);
            default:
                if ($valueType === 'object' && is_a($value, $expectedType)) {
                    return true;
                }
        }

        return false;
    }

}
