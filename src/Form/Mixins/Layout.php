<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\PropertyException;

trait Layout
{

    protected $layout;

    protected function defaultLayout()
    {
        return null;
    }

    public function layout()
    {
        return $this->layout;
    }

    protected function layouts(): array
    {
        return [];
    }

    protected function setLayout(string $layout = null)
    {
        if ($layout !== null && in_array($layout, $this->layouts()) === false) {
            throw new PropertyException('Invalid layout');
        }

        $this->layout = $layout;
        return $this;
    }

}
