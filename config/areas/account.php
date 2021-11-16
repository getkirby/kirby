<?php

return function () {
    return [
        'icon'      => 'account',
        'label'     => t('view.account'),
        'search'    => 'users',
        'dialogs'   => require __DIR__ . '/account/dialogs.php',
        'dropdowns' => require __DIR__ . '/account/dropdowns.php',
        'views'     => require __DIR__ . '/account/views.php'
    ];
};
