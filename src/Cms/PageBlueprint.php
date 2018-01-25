<?php

namespace Kirby\Cms;

class PageBlueprint extends Blueprint
{

    protected function convertOptionsToArray(): array
    {
        return $this->options()->toArray();
    }

    public function options()
    {
        return new PageBlueprintOptions(parent::options());
    }

}
