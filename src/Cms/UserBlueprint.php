<?php

namespace Kirby\Cms;

class UserBlueprint extends Blueprint
{

    public function options()
    {
        if (is_a($this->options, UserBlueprintOptions::class) === true) {
            return $this->options;
        }

        return $this->options = new UserBlueprintOptions($this->model, $this->options);
    }

}
