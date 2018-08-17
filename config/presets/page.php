<?php

return function ($props) {

    $section = function ($defaults, $props) {
        if ($props === true) {
            $props = [];
        }

        if (is_string($props) === true) {
            $props = [
                'headline' => $props
            ];
        }

        return array_replace_recursive($defaults, $props);
    };

    $sidebar = [];

    if (empty($props['cover']) === false) {
        $sidebar['cover'] = $section([
            'headline' => 'Cover',
            'type'     => 'files',
            'template' => 'cover',
            'layout'   => 'cards',
            'max'      => 1
        ], $props['cover']);
    }

    if (empty($props['meta']) === false) {
        $sidebar['meta'] = [
            'type'   => 'fields',
            'fields' => $props['meta']
        ];
    }

    if (empty($props['images']) === false) {
        $sidebar['images'] = $section([
            'headline' => 'Images',
            'type'     => 'files',
            'layout'   => 'list',
            'template' => 'image'
        ], $props['images']);
    }

    if (empty($sidebar) === true) {
        $props['fields'] = $props['fields'] ?? [];
    } else {
        $props['columns'] = [
            [
                'width'  => '2/3',
                'fields' => $props['fields'] ?? []
            ],
            [
                'width' => '1/3',
                'sections' => $sidebar
            ]
        ];

        unset(
            $props['fields'],
            $props['cover'],
            $props['meta'],
            $props['images']
        );

    }

    return $props;

};
