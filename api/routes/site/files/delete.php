<?php

return [
    'auth'    => true,
    'pattern' => 'site/files/(:any)',
    'method'  => 'DELETE',
    'action'  => function ($filename) {
        if ($file = $this->site()->file($filename)) {
            return $file->delete();
        }
    }
];
