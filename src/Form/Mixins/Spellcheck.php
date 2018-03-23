<?php

namespace Kirby\Form\Mixins;

trait Spellcheck
{

    protected $spellcheck;

    protected function defaultSpellcheck(): bool
    {
        return false;
    }

    protected function setSpellcheck(bool $spellcheck = null)
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    public function spellcheck()
    {
        return $this->spellcheck;
    }

}
