<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\Url;

class File extends \Kirby\Text\Tags\File
{

    use Dependencies;

    /**
     * Returns the list of allowed attributes for the image
     *
     * @return array
     */
    protected function link(): string
    {
        if ($file = $this->file($this->value())) {
            return $file->url();
        }

        return Url::to(parent::link());
    }

    protected function filename(): string
    {
        return basename($this->link());
    }

    protected function text(): string
    {
        return $this->attr('text', $this->filename());
    }

}
