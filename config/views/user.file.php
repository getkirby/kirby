<?php

/** @var \Kirby\Cms\App $kirby */

return function (string $id, string $filename) use ($kirby) {
    $filename = urldecode($filename);

    if (!$user = $kirby->user($id)) {
        return t('error.user.undefined');
    }

    return Inertia::file($user->file($filename));
};
