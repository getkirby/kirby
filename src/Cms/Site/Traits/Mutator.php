<?php

namespace Kirby\Cms\Site\Traits;

use Exception;

use Kirby\Cms\Site;

trait Mutator
{

    public function save(): Site
    {
        return $this->update();
    }

    public function update(array $data = [])
    {
        $data = array_merge($this->content()->data(), $data);

        $this->store->write($data);
        $this->content = $data;

        return $this;
    }

}
