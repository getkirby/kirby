<?php

namespace Kirby\Cms;

class SiteBlueprint extends Blueprint
{
    public function options()
    {
        if (is_a($this->options, SiteBlueprintOptions::class) === true) {
            return $this->options;
        }

        return $this->options = new SiteBlueprintOptions($this->model, $this->options);
    }
}
