<?php

namespace Kirby\Cms;

use Kirby\FileSystem\Folder;

class Assets
{

    public function link($source, $link): bool
    {
        $folder = new Folder(dirname($link));
        $folder->make(true);

        if (is_link($link) === true) {
            return true;
        }

        return link($source, $link);
    }

}
