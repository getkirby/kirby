<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\LogicException;
use Kirby\Image\Image;
use Kirby\Toolkit\F;

class AvatarStore extends AvatarStoreDefault
{
    public function asset()
    {
        return new Image($this->root(), $this->url());
    }

    public function create(Upload $upload)
    {
        // delete all public versions
        $this->avatar()->unpublish();

        // overwrite the original
        if (F::copy($upload->root(), $this->root(), true) !== true) {
            throw new Exception([
                'key' => 'avatar.create.fail',
            ]);
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
        $this->avatar()->unpublish();

        if (F::remove($this->root()) !== true) {
            throw new Exception([
                'key' => 'avatar.delete.fail',
            ]);
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

    public function replace(Upload $upload)
    {
        return $this->create($upload);
    }

    public function root(): string
    {
        return $this->kirby()->root('accounts') . '/' . $this->user()->email() . '/profile.jpg';
    }
}
