<?php

namespace Kirby\Cms;

class PageBlueprintOptions extends BlueprintObject
{

    public function schema(): array
    {
        return [
            'delete' => [
                'type'    => 'boolean',
                'default' => true
            ],
            'template' => [
                'type'    => 'boolean',
                'default' => true
            ],
            'url' => [
                'type'    => 'boolean',
                'default' => true
            ]
        ];
    }

}
