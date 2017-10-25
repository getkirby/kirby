<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

trait HasFiles
{

    public function audio(): Files
    {
        return $this->files()->filterBy('type', '==', 'audio');
    }

    public function code(): Files
    {
        return $this->files()->filterBy('type', '==', 'code');
    }

    public function documents(): Files
    {
        return $this->files()->filterBy('type', '==', 'document');
    }

    public function file(string $filename = null, string $in = 'files')
    {

        if ($filename === null) {
            return $this->$in()->first();
        }

        if (Str::contains($filename, '/')) {
            $path     = dirname($filename);
            $filename = basename($filename);
            return $this->find($path)->$in()->find($filename);
        }

        return $this->$in()->find($filename);
    }

    public function image(string $filename = null)
    {
        return $this->file($filename, 'images');
    }

    public function images(): Files
    {
        return $this->files()->filterBy('type', '==', 'image');
    }

    public function videos(): Files
    {
        return $this->files()->filterBy('type', '==', 'video');
    }

}
