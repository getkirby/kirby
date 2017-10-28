<?php

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/slug',
    'method'  => 'POST',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {
            return $this->output('page',
                $page->changeSlug($this->input('slug'))
            );
        }

    }
];
