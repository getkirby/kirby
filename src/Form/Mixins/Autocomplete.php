<?php

namespace Kirby\Form\Mixins;

trait Autocomplete
{

    protected $autocomplete;

    public function autocomplete()
    {
        return $this->autocomplete;
    }

    protected function defaultAutocomplete()
    {
        return null;
    }

    protected function setAutocomplete(string $autocomplete = null)
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

}
