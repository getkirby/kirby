<?php

return function ($kirby) {
    return [
        'icon'    => 'globe',
        'label'   => t('view.languages'),
        'menu'    => true,
        'dialogs' => require __DIR__ . '/languages/dialogs.php',
        'views'   => require __DIR__ . '/languages/views.php'
    ];
};
