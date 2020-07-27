<?php

use Kirby\Cms\Form;

return function ($path) use ($kirby) {

    if (!$page = $kirby->page(str_replace('+', '/', $path))) {
        return t('error.page.undefined');
    }

    return [
        'component' => 'PageView',
        'props'     => Inertia::model($page, [
            'next' => function () use ($page) {
                $next = $page
                    ->nextAll()
                    ->filterBy('intendedTemplate', $page->intendedTemplate())
                    ->filterBy('status', $page->status())
                    ->filterBy('isReadable', true)
                    ->first();

                return Inertia::prevnext($next, 'title');
            },
            'page' => [
                'content'    => Inertia::content($page),
                'id'         => $page->id(),
                'parent'     => $page->parentModel()->panelUrl(true),
                'previewUrl' => $page->previewUrl(),
                'status'     => $page->status(),
                'title'      => $page->title()->toString(),
            ],
            'prev'   => function () use ($page) {
                $prev = $page
                    ->prevAll()
                    ->filterBy('intendedTemplate', $page->intendedTemplate())
                    ->filterBy('status', $page->status())
                    ->filterBy('isReadable', true)
                    ->last();

                return Inertia::prevnext($prev, 'title');
            },
            'status' => function () use ($page) {
                if ($status = $page->status()) {
                    return $page->blueprint()->status()[$status] ?? null;
                }
            },
        ]),
        'view' => [
            'breadcrumb' => function () use ($page) {
                return Inertia::collect($page->parents()->flip()->merge($page), function ($parent) {
                    return [
                        'label' => $parent->title()->toString(),
                        'link'  => $parent->panelUrl(true),
                    ];
                });
            },
            'id'    => 'site',
            'title' => $page->title()->toString(),
        ]
    ];

};
