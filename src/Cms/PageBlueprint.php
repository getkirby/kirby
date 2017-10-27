<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class PageBlueprint extends Blueprint
{

    public function data()
    {
        $data = parent::data();

        // fallbacks for old blueprints

        return $data;
    }

}
