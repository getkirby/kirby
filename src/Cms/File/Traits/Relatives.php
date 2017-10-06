<?php

namespace Kirby\Cms\File\Traits;

use Exception;
use Kirby\Cms\Files;

trait Relatives
{

    protected $collection;

    public function collection(Files $collection = null)
    {
        if ($collection === null) {
            if (is_a($this->collection, Files::class)) {
                return $this->collection;
            }

            return $this->collection = $this->page()->files();
        }

        $this->collection = $collection;
        return $this;
    }

    public function page()
    {
        return $this->attributes['page'] ?? null;
    }

    public function site()
    {
        return $this->attributes['site'] ?? null;
    }

}
