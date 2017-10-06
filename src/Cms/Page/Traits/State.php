<?php

namespace Kirby\Cms\Page\Traits;

trait State
{

    public function isVisible(): bool
    {
        return $this->num() !== null;
    }

    public function isInvisible(): bool
    {
        return $this->num() === null;
    }

}
