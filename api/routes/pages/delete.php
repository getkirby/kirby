<?php

use Kirby\Cms\Assets\PageAssets;

return [
    'pattern' => 'pages/(:all)',
    'method'  => 'DELETE',
    'action'  => function ($path) {

        // try to find the page
        $page = $this->site()->find($path);

        // delete all assets for the page
        $assets = new PageAssets($this->app()->root('files'), $page);
        $assets->delete();

        // delete the page folder
        return $page->delete();

    }
];
