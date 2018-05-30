<?php

namespace Kirby\Cms;

use Kirby\Collection\Finder;
use Kirby\Toolkit\Str;

class UsersFinder extends Finder
{
    public function findByKey($key)
    {
        if (Str::contains($key, '@') === true) {
            $key = sha1($key);
        }

        return parent::findByKey($key);
    }
}
