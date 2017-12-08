<?php

use Kirby\Cms\Page;

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/children',
    'method'  => 'POST',
    'action'  => function ($path) {

        if ($parent = $this->site()->find($path)) {

            $page = Page::create([
                'parent'   => $parent,
                'slug'     => $this->input('slug'),
                'template' => $this->input('template'),
                'content'  => $this->input('content')
            ]);

            return $this->output('page', $page);

        }

    }
];
