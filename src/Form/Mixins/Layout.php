<?php

namespace Kirby\Form\Mixins;

use Kirby\Exception\InvalidArgumentException;

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
            throw new InvalidArgumentException([
                'key' => 'form.layout.invalid'
            ]);
        }

        $this->layout = $layout;
        return $this;
    }

}
