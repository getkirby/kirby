<?php

namespace Kirby\Form\Input;

class MultiSelectOptions extends SelectOptions
{

    public function select($values)
    {
        if (is_array($values) === false) {
            $values = [$values];
        }

        foreach ($this->data as $key => $option) {
            $this->data[$key]->set('selected', in_array($key, $values));
        }
        return $this;
    }

    public function selected()
    {
        return $this->filterBy('selected', true);
    }

}
