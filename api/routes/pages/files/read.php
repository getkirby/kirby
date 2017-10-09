<?php

return [
    'pattern' => 'pages/(:all)/files/(:any)',
    'action'  => function ($path, $filename) {
        if ($page = $this->site()->find($path)) {
            if ($file = $page->file($filename)) {
                return $this->output('file', $file);
            }
        }
    }
];
