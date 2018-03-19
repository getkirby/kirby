<?php

use Kirby\Cms\Children;
use Kirby\Cms\Files;
use Kirby\Cms\Locales;
use Kirby\Cms\Pages;
use Kirby\Cms\Roles;
use Kirby\Cms\Users;

/**
 * Api Collection Definitions
 */
return [

    /**
     * Children
     */
    'children' => [
        'model' => 'page',
        'type'  => Children::class,
        'view'  => 'compact'
    ],

    /**
     * Files
     */
    'files' => [
        'model' => 'file',
        'type'  => Files::class
    ],

    /**
     * Locales
     */
    'locales' => [
        'model' => 'locale',
        'type'  => Locales::class,
        'view'  => 'compact'
    ],

    /**
     * Pages
     */
    'pages' => [
        'model' => 'page',
        'type'  => Pages::class,
        'view'  => 'compact'
    ],

    /**
     * Roles
     */
    'roles' => [
        'model' => 'role',
        'type'  => Roles::class,
        'view'  => 'compact'
    ],

    /**
     * Users
     */
    'users' => [
        'default' => function () {
            return $this->users();
        },
        'model' => 'user',
        'type'  => Users::class,
        'view'  => 'compact'
    ]

];
