<?php

use Kirby\Cms\Auth;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;

return function () {
    $auth = $this->kirby()->auth();

    // csrf token check
    if ($auth->type() === 'session' && $auth->csrf() === false) {
        throw new PermissionException('Unauthenticated', 403);
    }

    // get user from session or basic auth
    if ($user = $auth->user()) {
        if ($user->role()->permissions()->for('access', 'panel') === false) {
            throw new PermissionException(['key' => 'access.panel']);
        }

        return $user;
    }

    throw new PermissionException('Unauthenticated', 403);
};
