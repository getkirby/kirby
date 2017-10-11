<?php

use Kirby\Panel\Options\Field;

return [
    'pattern' => 'panel/options/field',
    'action'  => function () {

        $result = new Field([
            'site'      => $this->site(),
            'page'      => $this->input('page'),
            'field'     => $this->input('field'),
            'separator' => $this->input('separator')
        ]);

        return array_map(function ($item) {
            return [
                'text'  => $item,
                'value' => $item
            ];
        }, $result->toArray());

    }
];
