<?php

use Kirby\Cms\Assets\PageAssets;

return [
    'pattern' => 'pages/(:all)/files/(:any)/rename',
    'method'  => 'POST',
    'action'  => function ($path, $filename) {

        if ($file = $this->site()->file($path . '/' . $filename)) {

            // delete all assets first to clean them up
            $assets = new PageAssets($this->app()->root('files'), $file->page());
            $assets->delete($filename);

            // rename the file and return the modified object
            return $this->output('file', $file->rename($this->input('name')));

        }

    }
];
