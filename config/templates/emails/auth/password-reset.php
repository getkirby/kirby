<?php

use Kirby\Toolkit\I18n;

/**
 * @var \Kirby\Cms\User $user
 * @var string $site
 * @var string $code
 * @var int $timeout
 */
echo I18n::template(
    'login.email.password-reset.body',
    null,
    compact('user', 'site', 'code', 'timeout'),
    $user->language()
);
