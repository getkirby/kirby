<?php

namespace Kirby\Form\Input;

class File extends Text
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'accept' => [
                'default'   => 'string',
                'attribute' => true
            ],
            'multiple' => [
                'type'      => 'boolean',
                'default'   => false,
                'attribute' => true
            ],
            'name' => [
                'default' => 'file'
            ],
            'type' => [
                'default' => 'file'
            ],
        ], ...$extend);
    }

}
