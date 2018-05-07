<?php

return [
    'debug'  => true,
    'thumbs' => [
        'driver' => 'im',
        'bin' => '/usr/local/bin/convert'
    ],
    'routes' => [
        [
            'pattern' => 'team/(:any)',
            'action'  => function ($hash) {
                return go('team/#' . $hash);
            }
        ],
        [
            'pattern' => 'one-pager/(:any)',
            'action'  => function ($hash) {
                return go('one-pager/#' . $hash);
            }
        ]
    ]
];
