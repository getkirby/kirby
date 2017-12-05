<?php

namespace Kirby\Form\Input;

use Kirby\Toolkit\V;

class Time extends Date
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'pattern' => [
                'default'   => '[0-9]{2}:[0-9]{2}',
            ],
            'step' => [
                'type'      => 'integer',
                'default'   => 60,
                'attribute' => true
            ],
            'type' => [
                'default'   => 'time',
            ],
        ], ...$extend);
    }

    public function fill($value) {
        return $this->set('value', $value);
    }

}
