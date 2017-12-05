<?php

namespace Kirby\Form\Input;

class Checkboxes extends Radios
{

    protected $options;

    public function checked($value): bool
    {
        return in_array($value, $this->options()->checked()->values());
    }

    public function fill($value)
    {
        if (is_array($value) === false) {
            $value = [$value];
        }

        $this->options()->check($value);
        $this->set('value', $value);

        return $value;
    }

    protected function optionsCollection($options)
    {
        return new CheckboxOptions($options);
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

}
