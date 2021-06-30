<?php

return function ($kirby) {
    return [
        'icon'    => 'settings',
        'label'   => t('view.settings'),
        'menu'    => true,
        'dialogs' => require __DIR__ . '/settings/dialogs.php',
        'views'   => require __DIR__ . '/settings/views.php'
    ];
};
