<?php

return function (array $props) {

    // load the general templates setting for all sections
    $templates = $props['templates'] ?? null;

    $section = function ($headline, $status, $props) use ($templates) {
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

        // inject the global templates definition
        if (empty($templates) === false) {
            $props['templates'] = $props['templates'] ?? $templates;
        }

        return array_replace_recursive($defaults, $props);
    };

    $sections = [];

    $drafts   = $props['drafts']   ?? [];
    $unlisted = $props['unlisted'] ?? false;
    $listed   = $props['listed']   ?? [];


    if ($drafts !== false) {
        $sections['drafts'] = $section(t('pages.status.draft'), 'drafts', $drafts);
    }

    if ($unlisted !== false) {
        $sections['unlisted'] = $section(t('pages.status.unlisted'), 'unlisted', $unlisted);
    }

    if ($listed !== false) {
        $sections['listed'] = $section(t('pages.status.listed'), 'listed', $listed);
    }

    // cleaning up
    unset($props['drafts'], $props['unlisted'], $props['listed'], $props['templates']);

    return array_merge($props, ['sections' => $sections]);
};
