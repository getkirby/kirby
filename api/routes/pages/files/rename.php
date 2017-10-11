<?php

return [
    'pattern' => 'pages/(:all)/files/(:any)/rename',
    'method'  => 'POST',
    'action'  => function ($path, $filename) {
        if ($page = $this->site()->find($path)) {
            if ($file = $page->file($filename)) {
                return $this->output('file', $file->rename($this->input('name')));
            }
        }
    }
];
