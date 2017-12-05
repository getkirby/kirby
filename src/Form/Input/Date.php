<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;
use Kirby\Toolkit\V;

class Date extends Input
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autocomplete' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'autofocus' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'disabled' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'max' => [
                'attribute' => true
            ],
            'min' => [
                'attribute' => true
            ],
            'name' => [
                'default'   => 'text',
                'attribute' => true
            ],
            'pattern' => [
                'type'      => 'string',
                'default'   => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
                'attribute' => true
            ],
            'placeholder' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'readonly' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'type' => [
                'type'      => 'string',
                'default'   => 'date',
                'attribute' => true
            ],
            'value' => [
                'type'      => 'string',
                'attribute' => true
            ]
        ], ...$extend);
    }

    public function fill($value)
    {
        if ($ts = strtotime($value)) {
            return $this->set('value', date('Y-m-d', $ts));
        }

        return $this->set('value', null);
    }

    public function validate($input): bool
    {
        return V::date($input);
    }

}
