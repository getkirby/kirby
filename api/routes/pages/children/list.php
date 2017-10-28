<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/children',
    'method'  => 'GET',
    'action'  => function ($path) {
        return $this->output('children', $this->site()->find($path), $this->query());
    }
];
