<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

use Kirby\Exception\Exception;

class AvatarStoreDefault extends Store
{
    public function asset()
    {
        return new Image('profile.jpg', $this->url());
    }

    public function avatar()
    {
        return $this->model;
    }

    public function create(Upload $upload)
    {
        return $this->avatar();
    }

    public function delete(): bool
    {
        throw new Exception([
            'key' => 'avatar.delete.fail',
        ]);
    }

    public function exists(): bool
    {
        return false;
    }

    public function id()
    {
        return $this->user()->id();
    }

    public function replace(Upload $upload)
    {
        return $this->create($upload);
    }

    public function thumb(array $options = [])
    {
        throw new Exception([
            'key' => 'avatar.thumb.fail',
        ]);
    }

    public function url(): string
    {
        return $this->user()->mediaUrl() . '/profile.jpg';
    }

    public function user()
    {
        return $this->model->user();
    }
}
