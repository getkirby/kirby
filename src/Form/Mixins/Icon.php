<?php

namespace Kirby\Form\Mixins;

trait Icon
{

    protected $icon;

    protected function defaultIcon()
    {
        return null;
    }

    public function icon()
    {
        return $this->icon;
    }

    /**
     * Set the field icon
     *
     * @param string $icon
     * @return self
     */
    protected function setIcon(string $icon = null)
    {
        $this->icon = $icon;
        return $this;
    }

}
