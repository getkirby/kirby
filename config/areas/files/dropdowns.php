<?php

use Kirby\Cms\Find;

return [
    'file' => function (string $parent, string $filename) {
        return Find::file($parent, $filename)->panel()->dropdown();
    }
];
