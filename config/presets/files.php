<?php

return function (array $props) {
    $props['sections'] = [
        'files' => [
            'headline' => $props['headline'] ?? t('files'),
            'type'     => 'files',
            'layout'   => $props['layout'] ?? 'cards',
            'template' => $props['template'] ?? null,
            'image'    => $props['image'] ?? null,
            'info'     => '{{ file.dimensions }}'
        ]
    ];

    // remove global options
    unset(
        $props['headline'],
        $props['layout'],
        $props['template'],
        $props['image']
    );

    return $props;
};
