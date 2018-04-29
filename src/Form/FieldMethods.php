<?php

namespace Kirby\Form;

class FieldMethods extends FieldDefinition
{

    public function defaults(): array
    {
        return [
            'toApi' => function ($value) {
                return $value;
            },
            'toString' => function ($value) {
                return $value;
            },
            'validate' => function () {
                return;
            }
        ];
    }

}
