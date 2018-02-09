<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;
use Kirby\Util\F;

class AvatarStore extends AvatarStoreDefault
{

    public function asset()
    {
        return new Image($this->root(), $this->url());
    }

    public function create(string $source)
    {
        if ($this->exists() === true) {
            throw new Exception('The avatar already exists');
        }

        // delete all public versions
        $this->media()->delete($this->user());

        // overwrite the original
        if (F::copy($source, $this->root()) !== true) {
            throw new Exception('The avatar could not be created');
        }

        // return a fresh clone
        return $this->avatar()->clone();
    }

    public function delete(): bool
    {
        if ($this->exists() === false) {
            return true;
        }

        // delete all public versions
        $this->media()->delete($this->user());

        if (F::remove($this->root()) !== true) {
            throw new Exception('The avatar could not be deleted');
        }

        return true;
    }

    public function exists(): bool
    {
        return is_file($this->root()) === true;
    }

    public function id()
    {
        return $this->root();
    }

    public function replace(string $source)
    {
        $this->delete();
        return $this->create($source);
    }

    public function root(): string
    {
        return $this->kirby()->root('accounts') . '/' . $this->user()->email() . '/profile.jpg';
    }

    public function thumb(array $options = [])
    {
        return $this->media()->create($this->user(), $this->avatar(), $options);
    }

}
