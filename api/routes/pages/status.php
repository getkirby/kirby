<?php

return [
    'pattern' => 'pages/(:all)/status',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {
            return $this->output('page',
                $page->status(
                    $this->input('status'),
                    $this->input('position')
                )
            );
        }

    }
];
