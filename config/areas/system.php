<?php

return function ($kirby) {
    return [
        'icon'    => 'settings',
        'label'   => t('view.system'),
        'menu'    => true,
        'dialogs' => require __DIR__ . '/system/dialogs.php',
        'views'   => require __DIR__ . '/system/views.php'
    ];
};
