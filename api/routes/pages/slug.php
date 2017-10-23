<?php

use Kirby\Cms\Assets\PageAssets;

return [
    'pattern' => 'pages/(:all)/slug',
    'method'  => 'POST',
    'action'  => function ($path) {

        if ($page = $this->site()->find($path)) {

            // delete all page assets first
            $assets = new PageAssets($this->app()->root('files'), $page);
            $assets->delete();

            // move the page to the new place
            $page->move($this->input('slug'));

            // return the updated page object
            return $this->output('page', $page);

        }

    }
];
