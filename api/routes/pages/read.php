<?php

return [
    'pattern' => 'pages/(:all)',
    'action'  => function ($path) {
        return $this->output('page', $this->site()->find($path));
    }
];
