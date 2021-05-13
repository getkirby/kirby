<?php

/** @var \Kirby\Cms\App $kirby */

return function (string $filename) use ($kirby) {
    $filename = urldecode($filename);
    $file     = $kirby->site()->file($filename);

    return Inertia::file($file);
};
