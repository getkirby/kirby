<?php

use Kirby\Cms\PageBlueprint;

PageBlueprint::$presets['page'] = function ($props) {
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

    if (empty($props['images']) === false) {
        $sidebar['images'] = $section([
            'headline' => 'Images',
            'type'     => 'files',
            'layout'   => 'list',
            'template' => 'image'
        ], $props['images']);
    }

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
        $props['images']
    );

    return $props;
};

PageBlueprint::$presets['pages'] = function ($props) {
    $section = function ($headline, $status, $props) {
        $defaults = [
            'headline' => $headline,
            'type'     => 'pages',
            'layout'   => 'list',
            'status'   => $status
        ];

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

    $sections = [];

    if (empty($props['drafts']) === false) {
        $sections['drafts'] = $section('Drafts', 'drafts', $props['drafts']);
    }

    if (empty($props['unlisted']) === false) {
        $sections['unlisted'] = $section('Unlisted', 'unlisted', $props['unlisted']);
    }

    if (empty($props['listed']) === false) {
        $sections['listed'] = $section('Published', 'listed', $props['listed']);
    }

    // cleaning up
    unset($props['drafts'], $props['unlisted'], $props['listed']);

    return array_merge($props, ['sections' => $sections]);
};
