<?php

return [
    'pattern' => 'pages/(:all)/files/search',
    'method'  => 'POST',
    'action'  => function ($path) {
        return $this->output('files', $this->site()->find($path), $this->input());
    }
];
