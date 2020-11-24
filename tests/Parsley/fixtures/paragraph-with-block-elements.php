<?php

return [
    [
        'content' => [
            'text' => '<p>First <strong>line</strong></p>',
        ],
        'type' => 'text',
    ],
    [
        'content' => [
            'alt'      => null,
            'caption'  => null,
            'link'     => null,
            'location' => 'web',
            'src'      => 'image.jpg',
        ],
        'type' => 'image',
    ],
    [
        'content' => [
            'text' => '<p><i>Second</i> line</p>' . "\n\n" . '<p>Third line</p>',
        ],
        'type' => 'text',
    ]
];
