<?php

return function ($id) use ($kirby) {
    return [
        'component' => 'PluginView',
        'view'      => $id,
        'props' => [
            'id' => $id
        ]
    ];
};
