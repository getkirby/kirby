<?php

namespace Kirby\Cms;

trait HasContent
{

    public function __call(string $method, array $arguments = [])
    {
        if ($this->hasProp($method)) {
            return $this->prop($method, $arguments);
        }

        if ($this->hasPlugin($method)) {
            return $this->plugin($method, $arguments);
        }

        return $this->content()->get($method, $arguments);
    }

}
