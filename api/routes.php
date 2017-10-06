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

    // users
    require __DIR__ . '/routes/users/list.php',

];
