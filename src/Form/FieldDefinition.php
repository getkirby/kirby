<?php

namespace Kirby\Form;

use Closure;

abstract class FieldDefinition
{

    protected $field;
    protected $definition = [];

    public function __call(string $name, array $arguments)
    {
        if ($this->has($name) === true) {
            if (is_a($this->definition[$name], Closure::class) === true) {
                return $this->definition[$name]->call($this->field, ...$arguments);
            }

            return $this->definition[$name];
        }

        return null;
    }

    public function __construct(Field $field, array $arguments)
    {
        $this->field = $field;
        $this->definition = array_merge(
            $this->defaults(),
            $arguments
        );
    }

    abstract public function defaults(): array;

    public function has(string $name): bool
    {
        return isset($this->definition[$name]) === true;
    }

    public function toArray(): array
    {
        $array = $this->definition;

        // keep it tidy
        ksort($array);

        return $array;
    }
}
