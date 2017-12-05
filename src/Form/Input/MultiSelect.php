<?php

namespace Kirby\Form\Input;

class MultiSelect extends Select
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'name' => [
                'default' => 'multiselect',
            ],
            'multiple' => [
                'default' => true
            ]
        ], ...$extend);
    }

    public function fill($value)
    {
        if (is_array($value) === false) {
            $value = [$value];
        }

        $this->options()->select($value);
        return parent::fill($value);
    }

    public function validate($input): bool
    {
        foreach ($input as $value) {
            if ($this->options()->find($value) === null) {
                return false;
            }
        }

        return true;
    }

    public function options()
    {
        if (is_a($this->options, MultiSelectOptions::class)) {
            return $this->options;
        }

        return $this->options = new MultiSelectOptions($this->prop('options'));
    }

    public function selected($value): bool
    {
        return in_array($value, $this->options()->selected()->values());
    }

}
