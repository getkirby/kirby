<?php

return [
    'pattern' => 'pages/(:all)/status',
    'method'  => 'POST',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {
            return $this->output('page',
                $page->changeStatus(
                    $this->input('status'),
                    $this->input('position')
                )
            );
        }

    }
];
