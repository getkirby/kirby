<?php

use Kirby\Toolkit\I18n;

return function (array $props) {
    $props['sections'] = [
        'files' => [
            'headline' => $props['headline'] ?? I18n::translate('files'),
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
