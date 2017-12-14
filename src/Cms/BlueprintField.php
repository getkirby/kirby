<?php

namespace Kirby\Cms;

class BlueprintField extends BlueprintObject
{
    public function schema(): array
    {
        return [
            'label' => [
                'type'     => 'string',
                'required' => false
            ],
            'name' => [
                'type'     => 'string',
                'required' => true
            ],
            'type' => [
                'type'     => 'string',
                'required' => true,
            ],
        ];
    }
}
