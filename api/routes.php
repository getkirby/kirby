<?php

return [

    // blueprints
    require __DIR__ . '/routes/blueprints/list.php',
    require __DIR__ . '/routes/blueprints/read.php',

    // site: children
    require __DIR__ . '/routes/site/children/list.php',
    require __DIR__ . '/routes/site/children/search.php',
    require __DIR__ . '/routes/site/children/create.php',

    // site: files
    require __DIR__ . '/routes/site/files/list.php',
    require __DIR__ . '/routes/site/files/search.php',
    require __DIR__ . '/routes/site/files/read.php',
    require __DIR__ . '/routes/site/files/delete.php',

    // site
    require __DIR__ . '/routes/site/read.php',
    require __DIR__ . '/routes/site/update.php',

    // pages: children
    require __DIR__ . '/routes/pages/children/list.php',
    require __DIR__ . '/routes/pages/children/search.php',
    require __DIR__ . '/routes/pages/children/create.php',

    // pages: files
    require __DIR__ . '/routes/pages/files/list.php',
    require __DIR__ . '/routes/pages/files/create.php',
    require __DIR__ . '/routes/pages/files/search.php',
    require __DIR__ . '/routes/pages/files/rename.php',
    require __DIR__ . '/routes/pages/files/update.php',
    require __DIR__ . '/routes/pages/files/read.php',
    require __DIR__ . '/routes/pages/files/meta.php',
    require __DIR__ . '/routes/pages/files/delete.php',

    // pages: options
    require __DIR__ . '/routes/pages/options.php',
    require __DIR__ . '/routes/pages/blueprints.php',
    require __DIR__ . '/routes/pages/slug.php',
    require __DIR__ . '/routes/pages/status.php',

    // pages
    require __DIR__ . '/routes/pages/read.php',
    require __DIR__ . '/routes/pages/update.php',
    require __DIR__ . '/routes/pages/delete.php',

    // avatars
    require __DIR__ . '/routes/avatars/create.php',
    require __DIR__ . '/routes/avatars/delete.php',

    // users
    require __DIR__ . '/routes/users/list.php',
    require __DIR__ . '/routes/users/search.php',
    require __DIR__ . '/routes/users/create.php',
    require __DIR__ . '/routes/users/read.php',
    require __DIR__ . '/routes/users/update.php',
    require __DIR__ . '/routes/users/delete.php',

    // panel
    require __DIR__ . '/routes/panel/autocomplete/tags.php',
    require __DIR__ . '/routes/panel/autocomplete/users.php',
    require __DIR__ . '/routes/panel/languages/read.php',
    require __DIR__ . '/routes/panel/languages/list.php',
    require __DIR__ . '/routes/panel/options/query.php',
    require __DIR__ . '/routes/panel/options/field.php',
    require __DIR__ . '/routes/panel/options/url.php',
    require __DIR__ . '/routes/panel/system/read.php',

    // session
    require __DIR__ . '/routes/session/create.php',
    require __DIR__ . '/routes/session/read.php',
    require __DIR__ . '/routes/session/delete.php',

];
