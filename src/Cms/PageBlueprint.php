<?php

namespace Kirby\Cms;

class PageBlueprint extends Blueprint
{

    public function options()
    {
        if (is_a($this->options, PageBlueprintOptions::class) === true) {
            return $this->options;
        }

        return $this->options = new PageBlueprintOptions($this->model, $this->options);
    }

}
