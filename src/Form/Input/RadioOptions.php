<?php

namespace Kirby\Form\Input;

use Kirby\Html\Element;

class RadioOptions extends SelectOptions
{

    public function __construct(array $options = [])
    {
        // convert text to a lable prop to keep it consistent with
        // select boxes and their options.
        $options = array_map(function ($option) {
            $option['wrapper'] = 'li';
            $option['label']   = $option['text'] ?? $option['label'] ?? $option['value'] ?? null;
            return $option;
        }, $options);

        foreach ($options as $props) {
            $option = $this->option($props);
            $this->append($option->value(), $option);
        }
    }

    public function option($props)
    {
        return new Radio($props);
    }

    public function select($value)
    {
        foreach ($this->data as $key => $option) {
            $this->data[$key]->set('checked', $value === $key);
        }
        return $this;
    }

    public function check($value)
    {
        return $this->select($value);
    }

    public function selected()
    {
        return $this->findBy('checked', true);
    }

    public function checked()
    {
        return $this->selected();
    }

    public function toHtml(): Element
    {
        $options = $this->toArray(function ($option) {
            return $option->toHtml();
        });

        return new Element('ul', $options);
    }

}
