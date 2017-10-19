<?php

return [
    'pattern' => 'pages/(:all)/slug',
    'method'  => 'POST',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {
            $page->move($this->input('slug'));
            return $this->output('page', $page);
        }

    }
];
