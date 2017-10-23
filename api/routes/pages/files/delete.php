<?php

use Kirby\Cms\Assets\PageAssets;

return [
    'pattern' => 'pages/(:all)/files/(:any)',
    'method'  => 'DELETE',
    'action'  => function ($path, $filename) {

        if ($file = $this->site()->file($path . '/' . $filename)) {

            // delete the asset first
            $assets = new PageAssets($this->app()->root('files'), $file->page());
            $assets->delete($filename);

            return $file->delete();

        }

    }
];
