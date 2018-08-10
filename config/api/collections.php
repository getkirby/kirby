<?php

use Kirby\Cms\Files;
use Kirby\Cms\Languages;
use Kirby\Cms\Pages;
use Kirby\Cms\Roles;
use Kirby\Cms\Translations;
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
        'type'  => Pages::class,
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
     * Languages
     */
    'languages' => [
        'model' => 'language',
        'type'  => Languages::class,
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
     * Translations
     */
    'translations' => [
        'model' => 'translation',
        'type'  => Translations::class,
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
