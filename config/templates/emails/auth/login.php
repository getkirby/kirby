<?php

/**
 * @var \Kirby\Cms\User $user
 * @var string $code
 * @var int $timeout
 */
echo I18n::template('login.email.login.body', null, compact('user', 'code', 'timeout'), $user->language());
