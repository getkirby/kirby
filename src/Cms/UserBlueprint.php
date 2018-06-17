<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for users.
 */
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
