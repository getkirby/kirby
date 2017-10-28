<?php

use Kirby\Cms\Page;

return [
    'auth'    => true,
    'pattern' => 'site/children',
    'method'  => 'POST',
    'action'  => function () {

        $page = Page::create(
            null,
            $this->input('slug'),
            $this->input('template'),
            $this->input('content')
        );

        return $this->output('page', $page);

    }
];
