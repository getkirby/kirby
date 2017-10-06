<?php

return [
    'pattern' => [
        'files(/)([^/]+?.[a-z]{2,4}$)',
        'files/(:all)/(.*?.[a-z]{2,4}$)',
    ],
    'action'  => function ($path = null, $filename = null) {
        if ($page = $this->site()->find($path)) {
            if ($file = $page->file($filename)) {
                return $this->output('file', $file);
            }
        }
    }
];
