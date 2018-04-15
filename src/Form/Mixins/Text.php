<?php

namespace Kirby\Form\Mixins;

use Kirby\Util\I18n;

trait Text
{
    protected $text;

    protected function defaultText(): string
    {
        return '';
    }

    protected function setText(string $text = null)
    {
        $this->text = I18n::translate($text);
        return $this;
    }

    public function text(): string
    {
        return markdown(kirbytext($this->text));
    }
}
