<?php

return function ($kirby) {
    return [
        'icon'      => 'users',
        'label'     => t('view.users'),
        'search'    => 'users',
        'menu'      => true,
        'dialogs'   => require __DIR__ . '/users/dialogs.php',
        'dropdowns' => require __DIR__ . '/users/dropdowns.php',
        'searches'  => require __DIR__ . '/users/searches.php',
        'views'     => require __DIR__ . '/users/views.php'
    ];
};
