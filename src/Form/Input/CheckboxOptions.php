<?php

namespace Kirby\Form\Input;

use Kirby\Html\Element;

class CheckboxOptions extends RadioOptions
{

    public function option($props)
    {
        return new Checkbox($props);
    }

    public function select($values)
    {
        if (is_array($values) === false) {
            $values = [$values];
        }

        foreach ($this->data as $key => $option) {
            $this->data[$key]->set('checked', in_array($key, $values));
        }

        return $this;
    }

    public function selected()
    {
        return $this->filterBy('checked', true);
    }

}
