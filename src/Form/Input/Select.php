<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;
use Kirby\Html\Element;

class Select extends Input
{

    protected $options;

    public function tag()
    {
        return 'select';
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
            'multiple' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'name' => [
                'default' => 'select',
            ],
            'options' => [
                'type'    => 'array',
                'default' => []
            ],
            'size' => [
                'type'      => 'integer',
                'attribute' => true
            ],
        ], ...$extend);
    }

    public function selected($value): bool
    {
        return $value === $this->value();
    }

    public function fill($value)
    {
        $this->options()->select($value);
        $this->set('value', $value);
        return $value;
    }

    public function options()
    {
        if (is_a($this->options, SelectOptions::class)) {
            return $this->options;
        }

        return $this->options = new SelectOptions($this->prop('options'));
    }

    public function validate($input): bool
    {

        if (is_scalar($input) === false) {
            return false;
        }

        return $this->options()->find($input) !== null;

    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // convert options to plain array
        $array['options'] = $this->options()->toArray(function ($option) {
            return $option->toArray();
        });

        return $array;

    }

    public function element()
    {
        $options = [];

        foreach ($this->options() as $option) {
            $options[] = $option->toHtml();
        }

        return new Element($this->tag(), $options, $this->attributes());
    }

}
