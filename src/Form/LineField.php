<?php

namespace Kirby\Form;

class LineField extends Field
{
    protected function defaultName(): string
    {
        return 'line';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'type' => $this->type(),
        ];
    }
}
