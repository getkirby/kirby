<?php

return [
    'pattern' => 'pages/(:all)/files',
    'method'  => ['GET', 'POST'],
    'action'  => function ($path) {
        return $this->output('files', $this->site()->find($path), $this->query());
    }
];
