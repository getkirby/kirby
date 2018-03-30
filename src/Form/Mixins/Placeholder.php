<?php

namespace Kirby\Form\Mixins;

trait Placeholder
{

    protected $placeholder;

    protected function defaultPlaceholder()
    {
        return null;
    }

    public function placeholder()
    {
        return $this->placeholder;
    }

    protected function setPlaceholder($placeholder = null)
    {
        $this->placeholder = $this->translate($placeholder);
        return $this;
    }

}
