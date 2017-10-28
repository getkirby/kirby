<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files/(:any)',
    'method'  => 'DELETE',
    'action'  => function ($path, $filename) {
        if ($file = $this->site()->file($path . '/' . $filename)) {
            return $file->delete();
        }
    }
];
