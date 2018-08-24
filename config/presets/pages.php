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

    $drafts   = $props['drafts']   ?? [];
    $unlisted = $props['unlisted'] ?? false;
    $listed   = $props['listed']   ?? [];


    if ($drafts !== false) {
        $sections['drafts'] = $section('Drafts', 'drafts', $drafts);
    }

    if ($unlisted !== false) {
        $sections['unlisted'] = $section('Unlisted', 'unlisted', $unlisted);
    }

    if ($listed !== false) {
        $sections['listed'] = $section('Published', 'listed', $listed);
    }

    // cleaning up
    unset($props['drafts'], $props['unlisted'], $props['listed']);

    return array_merge($props, ['sections' => $sections]);

};
