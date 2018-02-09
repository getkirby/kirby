<?php

namespace Kirby\Form\Mixins;

trait Text
{

    protected $text;

    protected function defaultText()
    {
        return null;
    }

    protected function setText($text = null)
    {
        $this->text = $this->i18n($text);
        return $this;
    }

    public function text()
    {
        return $this->text;
    }

}
