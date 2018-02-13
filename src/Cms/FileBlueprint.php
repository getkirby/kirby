<?php

namespace Kirby\Cms;

class FileBlueprint extends Blueprint
{

    public function options()
    {
        if (is_a($this->options, FileBlueprintOptions::class) === true) {
            return $this->options;
        }

        return $this->options = new FileBlueprintOptions($this->model, $this->options);
    }

}
