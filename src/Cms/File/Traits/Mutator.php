<?php

namespace Kirby\Cms\File\Traits;

trait Mutator
{

    public function delete(): bool
    {
        return $this->store->delete();
    }

}
