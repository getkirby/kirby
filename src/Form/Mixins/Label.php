<?php

namespace Kirby\Form\Mixins;

trait Label
{

    protected $label;

    protected function defaultLabel()
    {
        return null;
    }

    public function label()
    {
        return $this->label;
    }

    /**
     * Set the field label text
     *
     * @param string|array $label
     * @return self
     */
    protected function setLabel($label = null): self
    {
        $this->label = $this->i18n($label);
        return $this;
    }

}
