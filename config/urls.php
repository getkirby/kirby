<?php

use Kirby\Cms\Url;

return [
    'index' => function () {
        return Url::index();
    },
    'base' => function (array $urls) {
        return rtrim($urls['index'], '/');
    },
    'current' => function (array $urls) {
        $path = trim($this->path(), '/');

        if (empty($path) === true) {
            return $urls['index'];
        } else {
            return $urls['base'] . '/' . $path;
        }
    },
    'assets' => function (array $urls) {
        return $urls['base'] . '/assets';
    },
    'api' => function (array $urls) {
        return $urls['base'] . '/' . ($this->options['api']['slug'] ?? 'api');
    },
    'media' => function (array $urls) {
        return $urls['base'] . '/media';
    },
    'panel' => function (array $urls) {
        return $urls['base'] . '/' . ($this->options['panel']['slug'] ?? 'panel');
    }
];
