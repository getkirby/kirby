<?php

namespace Kirby\Form\Input;

class Checkbox extends Radio
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'name' => [
                'default' => 'checkbox',
            ],
            'type' => [
                'default' => 'checkbox'
            ]
        ], ...$extend);
    }

    public function checked(): bool
    {
        return filter_var($this->value(), FILTER_VALIDATE_BOOLEAN);
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes['checked'] = $this->checked();

        return $attributes;
    }

}
