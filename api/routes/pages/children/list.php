<?php

return [
    'pattern' => 'pages/(:all)/children',
    'method'  => ['GET', 'POST'],
    'action'  => function ($path) {
        return $this->output('children', $this->site()->find($path), $this->query());
    }
];
