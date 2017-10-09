<?php

namespace Kirby\Cms\Site\Traits;

use Kirby\Cms\File;
use Kirby\Cms\Files;

trait Assets
{

    protected $files;

    public function files(): Files
    {

        if (is_a($this->files, Files::class)) {
            return $this->files;
        }

        if (is_array($this->files)) {
            return $this->files = new Files($this->files, $this);
        }

        return $this->files = new Files($this->store->files(), $this);

    }

    public function images(): Files
    {
        return $this->files()->filterBy('type', '==', 'image');
    }

    public function videos(): Files
    {
        return $this->files()->filterBy('type', '==', 'video');
    }

    public function documents(): Files
    {
        return $this->files()->filterBy('type', '==', 'document');
    }

    public function audio(): Files
    {
        return $this->files()->filterBy('type', '==', 'audio');
    }

    public function code(): Files
    {
        return $this->files()->filterBy('type', '==', 'code');
    }

    public function file(string $filename = null)
    {
        return $filename === null ? $this->files()->first() : $this->files()->find($filename);
    }

    public function image(string $filename = null)
    {
        return $filename === null ? $this->images()->first() : $this->files()->find($filename);
    }

}
