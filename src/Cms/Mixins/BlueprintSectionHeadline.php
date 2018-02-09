<?php

namespace Kirby\Cms\Mixins;

trait BlueprintSectionHeadline
{

    protected $headline;

    public function headline(): string
    {
        return $this->headline;
    }

    protected function setHeadline($headline = null)
    {
        $this->headline = $this->stringTemplate($this->i18n($headline));
        return $this;
    }

}
