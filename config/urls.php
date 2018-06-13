<?php

use Kirby\Cms\Url;

return [
    'index' => function () {
        return Url::index();
    },
    'base' => function (array $urls) {
        return rtrim($urls['index'], '/');
    },
    'assets' => function (array $urls) {
        return $urls['base'] . '/assets';
    },
    'api' => function (array $urls) {
        return $urls['base'] . '/' . ($this->option('api')['folder'] ?? 'api');
    },
    'media' => function (array $urls) {
        return $urls['base'] . '/media';
    },
    'panel' => function (array $urls) {
        return $urls['base'] . '/' . ($this->option('panel')['folder'] ?? 'panel');
    }
];

