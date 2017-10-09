<?php

return [
    'pattern' => 'pages/(:all)/children/search',
    'method'  => 'POST',
    'action'  => function ($path) {
        return $this->output('children', $this->site()->find($path), $this->input());
    }
];
