<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files/(:any)',
    'action'  => function ($path, $filename) {

        if ($file = $this->site()->file($path . '/' . $filename)) {
            return $this->output('file', $file);
        }

    }
];
