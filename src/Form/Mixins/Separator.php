<?php

namespace Kirby\Form\Mixins;

trait Separator
{
    protected $separator;

    protected function defaultSeparator(): string
    {
        return ',';
    }

    public function separator(): string
    {
        return $this->separator;
    }

    protected function setSeparator(string $separator = ',')
    {
        $this->separator = $separator;
        return $this;
    }
}
