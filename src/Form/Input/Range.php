<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;
use Kirby\Toolkit\V;

class Range extends Input
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
            'max' => [
                'type'      => 'number',
                'attribute' => true
            ],
            'min' => [
                'type'      => 'number',
                'attribute' => true
            ],
            'name' => [
                'default' => 'range',
            ],
            'readonly' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'step' => [
                'type'      => 'number',
                'attribute' => true
            ],
            'type' => [
                'default'   => 'range',
                'attribute' => true
            ],
            'value' => [
                'type'      => 'number',
                'attribute' => true
            ]
        ], ...$extend);
    }

    public function validate($input): bool
    {

        if ($min = $this->min()) {
            if (V::min($input, $min) === false) {
                return false;
            }
        }

        if ($max = $this->max()) {
            if (V::max($input, $max) === false) {
                return false;
            }
        }

        return true;
    }

}
