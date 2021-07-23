<?php

return [
    'pages' => [
        'label' => t('pages'),
        'icon'  => 'page',
        'query' => function (string $query = null) {
            $pages   = site()->index(true)->search($query)->limit(10);
            $results = [];

            foreach ($pages as $page) {
                $results[] = [
                    'image' => $page->panel()->image(),
                    'text' => $page->title()->value(),
                    'link' => $page->panel()->url(true),
                    'info' => $page->id()
                ];
            }

            return $results;
        }
    ],
    'files' => [
        'label' => t('files'),
        'icon'  => 'image',
        'query' => function (string $query = null) {
            $files = site()
                ->index(true)
                ->filter('isReadable', true)
                ->files()
                ->search($query)
                ->limit(10);

            $results = [];

            foreach ($files as $file) {
                $results[] = [
                    'image' => $file->panel()->image(),
                    'text'  => $file->filename(),
                    'link'  => $file->panel()->url(true),
                    'info'  => $file->id()
                ];
            }

            return $results;
        }
    ]
];
