<?php

return function (array $props) {
    $props['sections'] = [
        'files' => [
            'headline' => $props['headline'] ?? t('files'),
            'type'     => 'files',
            'layout'   => $props['layout'] ?? 'cards',
            'info'     => '{{ file.dimensions }}'
        ]
    ];

    return $props;
};
