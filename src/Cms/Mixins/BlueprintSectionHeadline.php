<?php

namespace Kirby\Cms\Mixins;

use Kirby\Toolkit\I18n;

trait BlueprintSectionHeadline
{
    protected $headline;

    public function headline(): string
    {
        return $this->headline;
    }

    protected function setHeadline($headline = null)
    {
        $this->headline = $this->stringTemplate(I18n::translate($headline, $headline));
        return $this;
    }
}
