<?php

namespace Kirby\Cms\Mixins;

trait BlueprintSectionLayout
{
    protected $layout;

    protected function defaultLayout(): string
    {
        return 'list';
    }

    public function layout(): string
    {
        return $this->layout;
    }

    protected function setLayout($layout = null)
    {
        $this->layout = $layout;
        return $this;
    }
}
