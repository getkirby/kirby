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

}
