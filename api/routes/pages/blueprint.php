<?php

use Kirby\Cms\PageBlueprint;

return [
    'pattern' => 'pages/(:all)/blueprint',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {
            $blueprint = new PageBlueprint($page->template());
            return $blueprint->toArray();
        }

    }
];
