<?php

namespace Kirby\Users\User\Traits;

use Exception;

trait Mutator
{

    public function save(): self
    {
        $this->store->save($this->data()->data());
        return $this;
    }

    public function update(array $data): self
    {
        $data = array_merge($this->data()->data(), $data);

        $this->store->write($data);
        $this->data = $data;
        return $this;
    }

    public function delete(): bool
    {
        return $this->store->delete();
    }

}
