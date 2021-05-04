<?php

/**
 * @var \Kirby\Cms\User $user
 * @var string $code
 * @var int $timeout
 */
echo I18n::template('login.email.password-reset.body', null, compact('user', 'code', 'timeout'), $user->language());
