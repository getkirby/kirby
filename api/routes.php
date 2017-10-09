<?php

return [
    // site
    require __DIR__ . '/routes/site/children/list.php',
    require __DIR__ . '/routes/site/files/list.php',
    require __DIR__ . '/routes/site/files/read.php',
    require __DIR__ . '/routes/site/read.php',

    // pages: children
    require __DIR__ . '/routes/pages/children/list.php',

    // pages: files
    require __DIR__ . '/routes/pages/files/list.php',
    require __DIR__ . '/routes/pages/files/read.php',

    // pages: page
    require __DIR__ . '/routes/pages/update.php',
    require __DIR__ . '/routes/pages/read.php',
    //require 'pages/create.php',
    //require 'pages/update.php',
    //require 'pages/delete.php',

    // users
    require __DIR__ . '/routes/users/read.php',
    require __DIR__ . '/routes/users/update.php',
    require __DIR__ . '/routes/users/list.php',

    // panel
    require __DIR__ . '/routes/panel/languages/read.php',
    require __DIR__ . '/routes/panel/languages/list.php',

];
