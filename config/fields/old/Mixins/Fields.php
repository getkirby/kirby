<?php

namespace Kirby\Form\Mixins;

trait Fields
{
    protected $fields;

    protected function defaultFields(): array
    {
        return [];
    }

    public function fields()
    {
        return $this->fields;
    }

    protected function setFields(array $fields = null)
    {
        $this->fields = $fields;
        return $this;
    }
}
