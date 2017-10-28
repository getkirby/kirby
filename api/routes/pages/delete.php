<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)',
    'method'  => 'DELETE',
    'action'  => function ($path) {
        return $this->site()->find($path)->delete();
    }
];
