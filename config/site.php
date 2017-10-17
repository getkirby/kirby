<?php

use Kirby\Cms\Site;

return function () {
    return new Site([
        'url'  => $this->url(),
        'root' => $this->root('content')
    ]);
};
