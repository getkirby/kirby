<?php

namespace Kirby\Cms;

use Kirby\Util\Str;

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

    public function hasAudio()
    {
        return $this->audio()->count() > 0;
    }

    public function hasCode()
    {
        return $this->code()->count() > 0;
    }

    public function hasDocuments()
    {
        return $this->documents()->count() > 0;
    }

    public function hasFiles()
    {
        return $this->files()->count() > 0;
    }

    public function hasImages()
    {
        return $this->images()->count() > 0;
    }

    public function hasVideos()
    {
        return $this->videos()->count() > 0;
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
