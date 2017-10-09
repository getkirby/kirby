<?php

return [

    // blueprints
    require __DIR__ . '/routes/blueprints/list.php',
    require __DIR__ . '/routes/blueprints/read.php',

    // site: children
    require __DIR__ . '/routes/site/children/list.php',
    require __DIR__ . '/routes/site/children/create.php',

    // site: files
    require __DIR__ . '/routes/site/files/list.php',
    require __DIR__ . '/routes/site/files/read.php',
    require __DIR__ . '/routes/site/files/delete.php',

    // site
    require __DIR__ . '/routes/site/read.php',
    require __DIR__ . '/routes/site/update.php',

    // pages: children
    require __DIR__ . '/routes/pages/children/list.php',
    require __DIR__ . '/routes/pages/children/create.php',

    // pages: files
    require __DIR__ . '/routes/pages/files/list.php',
    require __DIR__ . '/routes/pages/files/read.php',
    require __DIR__ . '/routes/pages/files/delete.php',

    // pages
    require __DIR__ . '/routes/pages/read.php',
    require __DIR__ . '/routes/pages/update.php',
    require __DIR__ . '/routes/pages/delete.php',

    // users
    require __DIR__ . '/routes/users/list.php',
    require __DIR__ . '/routes/users/create.php',
    require __DIR__ . '/routes/users/read.php',
    require __DIR__ . '/routes/users/update.php',
    require __DIR__ . '/routes/users/delete.php',

    // panel
    require __DIR__ . '/routes/panel/languages/read.php',
    require __DIR__ . '/routes/panel/languages/list.php',

];
