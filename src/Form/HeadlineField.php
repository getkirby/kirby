<?php

namespace Kirby\Form;

class HeadlineField extends Field
{
    use Mixins\Label;

    protected $numbered;

    protected function defaultName(): string
    {
        return 'headline';
    }

    protected function defaultNumbered(): bool
    {
        return true;
    }

    public function isNumbered(): bool
    {
        return $this->numbered;
    }

    public function numbered(): bool
    {
        return $this->numbered;
    }

    protected function setNumbered(bool $numbered = true)
    {
        $this->numbered = $numbered;
        return $this;
    }
}
