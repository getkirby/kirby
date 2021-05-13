<?php

/** @var \Kirby\Cms\App $kirby */

return function (string $id, string $filename) use ($kirby) {
    $id       = str_replace('+', '/', $id);
    $filename = urldecode($filename);

    if (!$page = $kirby->page($id)) {
        return t('error.page.undefined');
    }

    return Inertia::file($page->file($filename));
};
