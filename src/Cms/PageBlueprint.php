<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class PageBlueprint extends Blueprint
{

    public function data()
    {
        $data = parent::data();

        if (empty($data['layout']) === true) {
            $data = (new PageBlueprintConverter($data))->toArray();
        }

        return $data;
    }

}
