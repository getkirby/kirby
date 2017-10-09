<?php

return [
    'pattern' => 'pages/(:all)/files/(:any)',
    'method'  => 'DELETE',
    'action'  => function ($path, $filename) {
        if ($file = $this->site()->find($path)->file($filename)) {
            return $file->delete();
        }
    }
];
