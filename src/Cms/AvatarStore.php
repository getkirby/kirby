<?php

namespace Kirby\Cms;

use Kirby\Image\Image;
use Kirby\Util\F;

use Kirby\Exception\Exception;

class AvatarStore extends AvatarStoreDefault
{
    public function asset()
    {
        return new Image($this->root(), $this->url());
    }

    public function create(Upload $upload)
    {
        // delete all public versions
        $this->media()->delete($this->user());

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
        $this->media()->delete($this->user());

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

    public function thumb(array $options = [])
    {
        return $this->media()->create($this->user(), $this->avatar(), $options);
    }
}
