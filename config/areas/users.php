<?php

return function ($kirby) {
    return [
        'icon'    => 'users',
        'label'   => t('view.users'),
        'search'  => 'users',
        'menu'    => true,
        'dialogs' => require __DIR__ . '/users/dialogs.php',
        'views'   => require __DIR__ . '/users/views.php'
    ];
};
