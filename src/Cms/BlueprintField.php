<?php

namespace Kirby\Cms;

class BlueprintField extends BlueprintObject
{
    public function schema(): array
    {
        return [
            'id' => [
                'type'    => 'string',
                'default' => function () {
                    return $this->name();
                },
            ],
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

    public function toArray(): array
    {
        $array = parent::toArray();

        // remove the reference to the collection
        unset($array['collection']);

        return $array;
    }

}
