<?php

namespace Kirby\Form\Input;

use Exception;
use Kirby\Form\Input;
use Kirby\Html\Element;

class Radio extends Input
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autofocus' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'disabled' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'label' => [
            ],
            'name' => [
                'default' => 'radio',
            ],
            'checked' => [
                'type'      => 'boolean',
                'default'   => false,
                'attribute' => true
            ],
            'readonly' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'type' => [
                'default'   => 'radio',
                'attribute' => true
            ],
            'value' => [
                'attribute' => true
            ],
            'wrapper' => [
                'default' => false
            ]
        ], ...$extend);
    }

    public function labelElement()
    {
        $label = $this->prop('label');

        if (is_string($label)) {
            $element = Element::create('label', $label);
        } else {
            $element = Element::create($label);
        }

        if ($element !== null) {
            $element->attr('for', $this->id());
        }

        return $element;
    }

    public function inputElement()
    {
        return parent::element();
    }

    public function wrapperElement()
    {
        return Element::create($this->prop('wrapper'));
    }

    public function element()
    {
        $input = $this->inputElement();
        $label = $this->labelElement();

        if ($label === null) {
            return $this->inputElement();
        }

        if ($wrapper = $this->wrapperElement()) {
            return $this->wrapperElement()->html([
                $input,
                $label
            ]);
        }

        return $label->html([
            $input,
            $label->html()
        ]);
    }

}
