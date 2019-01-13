<?php

return [
    // kirby
    'kirby' => function (array $roots) {
        return realpath(__DIR__ . '/../');
    },
    'translations' => function (array $roots) {
        return $roots['kirby'] . '/translations';
    },

    // index
    'index' => function (array $roots) {
        return realpath(__DIR__ . '/../../');
    },

    // assets
    'assets' => function (array $roots) {
        return $roots['index'] . '/assets';
    },

    // content
    'content' => function (array $roots) {
        return $roots['index'] . '/content';
    },

    // media
    'media' => function (array $roots) {
        return $roots['index'] . '/media';
    },

    // panel
    'panel' => function (array $roots) {
        return $roots['kirby'] . '/panel';
    },

    // site
    'site' => function (array $roots) {
        return $roots['index'] . '/site';
    },
    'accounts' => function (array $roots) {
        return $roots['site'] . '/accounts';
    },
    'blueprints' => function (array $roots) {
        return $roots['site'] . '/blueprints';
    },
    'cache' => function (array $roots) {
        return $roots['site'] . '/cache';
    },
    'collections' => function (array $roots) {
        return $roots['site'] . '/collections';
    },
    'config' => function (array $roots) {
        return $roots['site'] . '/config';
    },
    'controllers' => function (array $roots) {
        return $roots['site'] . '/controllers';
    },
    'emails' => function (array $roots) {
        return $roots['site'] . '/emails';
    },
    'languages' => function (array $roots) {
        return $roots['site'] . '/languages';
    },
    'models' => function (array $roots) {
        return $roots['site'] . '/models';
    },
    'plugins' => function (array $roots) {
        return $roots['site'] . '/plugins';
    },
    'sessions' => function (array $roots) {
        return $roots['site'] . '/sessions';
    },
    'snippets' => function (array $roots) {
        return $roots['site'] . '/snippets';
    },
    'templates' => function (array $roots) {
        return $roots['site'] . '/templates';
    },

    // blueprints
    'roles' => function (array $roots) {
        return $roots['blueprints'] . '/users';
    },
];
