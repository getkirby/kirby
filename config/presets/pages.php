<?php

return function (array $props) {

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
