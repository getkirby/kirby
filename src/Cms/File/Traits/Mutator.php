<?php

namespace Kirby\Cms\File\Traits;

trait Mutator
{

    public function update(array $data = []): self
    {
        $data = array_merge($this->meta()->data(), $data);

        $this->store->write($data);
        $this->meta = $data;

        return $this;
    }

    public function rename($name): self
    {
        if ($this->exists() === false || $name === $this->name()) {
            return $this;
        }

        $filename    = $this->filename();
        $this->asset = $this->store->rename($name);
        $this->root  = $this->asset->root();
        $this->id    = str_replace($filename, $this->asset->filename(), $this->id);

        return $this;
    }

    public function delete(): bool
    {
        return $this->store->delete();
    }

}
