<?php

return [
    'pattern' => 'site/files/(:any)',
    'action'  => function ($path, $filename) {
        if ($file = $this->site()->file($filename)) {
            return $this->output('file', $file);
        }
    }
];
