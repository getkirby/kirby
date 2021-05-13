<?php

/** @var \Kirby\Cms\App $kirby */

return function () use ($kirby) {
    $site = $kirby->site();

    return [
        'component' => 'SiteView',
        'props'     => Inertia::model($site, [
            'site' => [
                'previewUrl' => $site->previewUrl(),
                'title'      => $site->title()->toString()
            ]
        ]),
        'view' => 'site'
    ];
};
