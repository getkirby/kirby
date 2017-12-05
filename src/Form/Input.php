<?php

namespace Kirby\Form;

use Exception;
use Kirby\Html\Element;
use Kirby\Toolkit\V;

class Input extends Component
{

    public function __construct(array $props = [])
    {
        parent::__construct($props, $this->schema());
    }

    public function tag()
    {
        return 'input';
    }

    public function schema(...$extend): array
    {
        return array_replace_recursive([
            'class' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'id' => [
                'type'      => 'string',
                'attribute' => true,
                'default'   => function () {
                    return $this->name();
                }
            ],
            'name' => [
                'type'      => 'string',
                'required'  => true,
                'attribute' => true
            ],
            'required' => [
                'type'      => 'boolean',
                'default'   => false,
                'attribute' => true
            ]
        ], ...$extend);
    }

    public function fill($value)
    {
        $this->set('value', $value);
        return $value;
    }

    public function value($value = null)
    {
        // getter
        if ($value === null) {
            $value = $this->prop('value');

            if ($value === null || $value === '') {
                return $this->fill($this->prop('default'));
            }

            return $value;
        }

        // setter
        return $this->fill($value);
    }

    public function accepts($input): bool
    {
        if ($this->required() === true) {
            if ($input === null || $input === '') {
                return false;
            }
        }

        try {
            $this->validateProp('value', $input);
        } catch (Exception $e) {
            return false;
        }

        if ($rules = $this->prop('validate')) {
            try {
                V::value($input, $rules);
            } catch (Exception $e) {
                return false;
            }
        }

        return $this->validate($input);
    }

    protected function validate($input): bool
    {
        return true;
    }

    public function invalid(): bool
    {
        return $this->valid() === false;
    }

    public function valid(): bool
    {
        return $this->accepts($this->value());
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['valid'] = $this->valid();
        $array['value'] = $this->value();

        return $array;
    }

}
