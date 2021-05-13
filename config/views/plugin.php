<?php

/** @var \Kirby\Cms\App $kirby */

return function (string $id) use ($kirby) {
    return [
        'component' => 'PluginView',
        'view'      => $id,
        'props' => [
            'id' => $id
        ]
    ];
};
