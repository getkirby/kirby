<?php

use Kirby\Cms\Form;

return function ($userId, $filename) use ($kirby) {
    $filename = urldecode($filename);

    if (!$user = $kirby->user($userId)) {
        return t('error.user.undefined');
    }

    return Inertia::file($user->file($filename));
};
