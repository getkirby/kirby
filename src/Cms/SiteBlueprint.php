<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle the blueprint for the site.
 */
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
