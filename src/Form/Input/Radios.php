<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;
use Kirby\Html\Element;

class Radios extends Input
{

    protected $options;

    public function __construct(array $props)
    {

        parent::__construct($props);

        $options = [];

        // normalize all options
        foreach ($props['options'] as $index => $option) {
            if ($index === 0 && $this->prop('autofocus') === true) {
                $option['autofocus'] = true;
            }

            $option['disabled'] = $this->prop('disabled');
            $option['id']       = $this->prop('name') . '-' . $index;
            $option['name']     = $this->prop('name');
            $option['required'] = $this->prop('required');
            $option['wrapper']  = 'li';

            $options[] = $option;
        }

        $this->options = $this->optionsCollection($options);

    }

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
            'legend' => [
                'default' => false
            ],
            'options' => [
                'type'    => 'array',
                'default' => []
            ]
        ], ...$extend);
    }

    public function checked($value): bool
    {
        return $value === $this->value();
    }

    public function fill($value)
    {
        $this->options()->check($value);
        $this->set('value', $value);
        return $value;
    }

    public function options()
    {
        return $this->options;
    }

    protected function optionsCollection($options)
    {
        return new RadioOptions($options);
    }

    public function validate($input): bool
    {
        if (is_scalar($input) === false) {
            return false;
        }

        return $this->options()->find($input) !== null;
    }

    public function legendElement()
    {
        $legend = $this->prop('legend');

        if (is_string($legend)) {
            return Element::create('legend', $legend);
        } else {
            return Element::create($legend);
        }
    }

    public function element()
    {
        return new Element('fieldset', [$this->legendElement(), $this->options()->toHtml()], [
            'class' => $this->prop('class')
        ]);
    }

}
