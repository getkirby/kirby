<?php

namespace Kirby\Form\Mixins;

use Kirby\Util\I18n;

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
        $this->placeholder = I18n::translate($placeholder, $placeholder);
        return $this;
    }

}
