<?php

namespace Kirby\Form;

class InfoField extends Field
{

    use Mixins\Label;

    protected $text;

    protected function defaultText(): string
    {
        return '';
    }

    protected function setText(string $text = null)
    {
        $this->text = $text;
        return $this;
    }

    public function text(): string
    {
        return markdown(kirbytext($this->text));
    }

}
