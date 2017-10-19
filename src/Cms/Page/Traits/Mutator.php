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

    public function move(string $slug)
    {
        if (empty($slug)) {
            throw new Exception('The slug is missing');
        }

        $this->attributes['root'] = $this->store->move($slug);

        $parentId  = dirname($this->id());
        $parentUrl = dirname($this->url());

        if ($parentId === '.') {
            $parentId = null;
        }

        if ($parentUrl === '.') {
            $parentUrl = null;
        }

        $this->url = $parentUrl . '/' . $slug;
        $this->id  = ltrim($parentId . '/' . $slug, '/');

        return $this;
    }

    public function delete()
    {
        return $this->store->delete();
    }

}
