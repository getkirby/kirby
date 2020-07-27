<?php

use Kirby\Cms\Form;

return function ($filename) use ($kirby) {
    $filename = urldecode($filename);
    $file     = $kirby->site()->file($filename);

    return Inertia::file($file);
};
