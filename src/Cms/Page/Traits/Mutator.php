<?php

namespace Kirby\Cms\Page\Traits;

use Exception;

use Kirby\Cms\Page;

trait Mutator
{

    public function save(): Page
    {
        if ($this->store->exists()) {
            $this->update();
        } else {
            $this->store->create();
        }
        return $this;
    }

    public function update(array $data = [])
    {
        $data = array_merge($this->content()->data(), $data);

        $this->store->write($data);
        $this->content = $data;

        return $this;
    }

    public function rename()
    {

    }

    public function move()
    {

    }

    public function delete()
    {
        return $this->store->delete();
    }

}
