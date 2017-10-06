<?php

namespace Kirby\Cms\File\Traits;

use Kirby\Fields\Field;
use Kirby\Fields\Fields;

trait Meta
{

    protected $meta;

    public function meta()
    {

        if (is_a($this->meta, Fields::class)) {
            return $this->meta;
        }

        if (!is_array($this->meta)) {
            $this->meta = $this->store->read();
        }

        return $this->meta = new Fields($this->meta, function ($key, $value) {
            return new Field($key, $value);
        });

    }

}
