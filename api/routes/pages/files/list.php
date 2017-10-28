<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files',
    'method'  => 'GET',
    'action'  => function ($path) {
        return $this->output('files', $this->site()->find($path), $this->query());
    }
];
