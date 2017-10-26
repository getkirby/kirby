<?php

return [
    'pattern' => 'pages/(:all)/files/(:any)/options',
    'action'  => function ($path, $filename) {

        $file    = $this->site()->file($path . '/' . $filename);
        $options = [];

        // edit
        $options[] = [
            'icon'  => 'edit',
            'text'  => 'Edit',
            'link'  => '#',
            'click' => 'edit'
        ];

        // download
        $options[] = [
            'icon'  => 'download',
            'text'  => 'Download',
            'link'  => '#',
            'click' => 'download'
        ];

        // replace
        $options[] = [
            'icon'  => 'upload',
            'text'  => 'Replace',
            'link'  => '#',
            'click' => 'replace'
        ];

        // delete
        $options[] = [
            'icon'  => 'trash',
            'text'  => 'Delete',
            'link'  => '#',
            'click' => 'remove'
        ];

        return $options;

    }
];
