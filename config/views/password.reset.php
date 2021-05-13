<?php

/** @var \Kirby\Cms\App $kirby */

return function () use ($kirby) {
    return [
        'component' => 'ResetPasswordView',
        'view'      => 'reset-password'
    ];
};
