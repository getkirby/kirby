<?php

return function (array $props) {

    $props['sections'] = [
        'gallery' => [
            'headline' => $props['headline'] ?? 'Gallery',
            'type'     => 'files',
            'template' => $props['template'] ?? 'image',
            'layout'   => $props['layout']   ?? 'cards',
            'info'     => '{{ file.dimensions }}'
        ]
    ];

    return $props;

};
