<?php

use Kirby\Cms\Page;

return [
    'pattern' => 'pages/(:all)/children',
    'method'  => 'POST',
    'action'  => function ($path) {

        if ($parent = $this->site()->find($path)) {

            $page = Page::create(
                $parent,
                $this->input('slug'),
                $this->input('template'),
                $this->input('content')
            );

            return $this->output('page', $page);

        }

    }
];
