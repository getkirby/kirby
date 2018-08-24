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

    if (empty($props['pages']) === false) {
        $sidebar['pages'] = $section([
            'headline' => 'Pages',
            'type'     => 'pages',
            'status'   => 'all',
            'layout'   => 'list',
        ], $props['pages']);
    }

    if (empty($props['files']) === false) {
        $sidebar['files'] = $section([
            'headline' => 'Files',
            'type'     => 'files',
            'layout'   => 'list'
        ], $props['files']);
    }

    if (empty($sidebar) === true) {
        $props['fields'] = $props['fields'] ?? [];
    } else {
        $props['columns'] = [
            [
                'width' => '1/3',
                'sections' => $sidebar
            ],
            [
                'width'  => '2/3',
                'fields' => $props['fields'] ?? []
            ]
        ];

        unset(
            $props['fields'],
            $props['files'],
            $props['pages']
        );

    }

    return $props;

};
