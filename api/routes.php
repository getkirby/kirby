<?php

return [
    // site
    require __DIR__ . '/routes/site/read.php',

    // pages
    require __DIR__ . '/routes/pages/read.php',
    //require 'pages/create.php',
    //require 'pages/update.php',
    //require 'pages/delete.php',

    // children
    require __DIR__ . '/routes/children/read.php',

    // files
    require __DIR__ . '/routes/files/read.php',
    require __DIR__ . '/routes/files/list.php',

    // users
    require __DIR__ . '/routes/users/read.php',
    require __DIR__ . '/routes/users/list.php',

    // languages
    require __DIR__ . '/routes/languages/read.php',
    require __DIR__ . '/routes/languages/list.php',

];
