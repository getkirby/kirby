<?php

return [
    'auth'    => true,
    'pattern' => 'site/files/(:any)',
    'action'  => function ($filename) {
        if ($file = $this->site()->file($filename)) {
            return $this->output('file', $file);
        }
    }
];
