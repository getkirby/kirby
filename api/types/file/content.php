<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return function ($file) {

    $content = $file->content()->toArray();

    return $content;

};
