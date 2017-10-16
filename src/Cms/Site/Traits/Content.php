<?php

namespace Kirby\Cms\Site\Traits;

use Kirby\Fields\Field;
use Kirby\Fields\Fields;

trait Content
{

    protected $content = null;

    public function content()
    {

        if (is_a($this->content, Fields::class)) {
            return $this->content;
        }

        if (is_array($this->content)) {
            // convert data arrays to field objects
            $content = $this->content;
        } elseif (isset($this->attributes['content']) && is_array($this->attributes['content'])) {
            // take content from the passed attributes first
            $content = $this->attributes['content'];
        } else {
            // read content from the store
            $content = $this->store->read();
        }

        return $this->content = new Fields($content, function ($key, $value) {
            return new Field($key, $value, [
                'site' => $this
            ]);
        });

    }

}
