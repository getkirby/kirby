<?php

use Kirby\Cms\Form;

return function ($pageId, $filename) use ($kirby) {
    $pageId   = str_replace('+', '/', $pageId);
    $filename = urldecode($filename);

    if (!$page = $kirby->page($pageId)) {
        return t('error.page.undefined');
    }

    return Inertia::file($page->file($filename));
};
