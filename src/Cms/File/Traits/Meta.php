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

        if (is_array($this->meta)) {
            // convert data arrays to field objects
            $meta = $this->meta;
        } elseif (isset($this->attributes['meta']) && is_array($this->attributes['meta'])) {
            // take meta from the passed attributes first
            $meta = $this->attributes['meta'];
        } else {
            // read meta from the store
            $meta = $this->store->read();
        }

        return $this->meta = new Fields($meta, function ($key, $value) {
            return new Field($key, $value, [
                'file' => $this,
                'page' => $page = $this->page(),
                'site' => $page->site()
            ]);
        });

    }

}
