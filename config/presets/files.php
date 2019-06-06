<?php

return function (array $props) {
    $props['sections'] = [
        'files' => [
            'headline' => $props['headline'] ?? t('files'),
            'type'     => 'files',
            'layout'   => $props['layout'] ?? 'cards',
            'template' => $props['template'] ?? null,
            'info'     => '{{ file.dimensions }}'
        ]
    ];

    return $props;
};
