<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files/(:any)/rename',
    'method'  => 'POST',
    'action'  => function ($path, $filename) {

        if ($file = $this->site()->file($path . '/' . $filename)) {
            // rename the file and return the modified object
            return $this->output('file', $file->rename($this->input('name')));
        }

    }
];
