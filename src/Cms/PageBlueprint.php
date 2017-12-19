<?php

namespace Kirby\Cms;

class PageBlueprint extends Blueprint
{

    public function options()
    {
        return new PageBlueprintOptions($this->props('options'));
    }

}
