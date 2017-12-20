<?php

use Kirby\Cms\PageBlueprint;
use Kirby\Cms\Form;

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/blueprint',
    'action'  => function ($path) {
        if ($page = $this->site()->find($path)) {
            return $page->blueprint()->toArray();
        }
    }
];
