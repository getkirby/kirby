<?php

use Kirby\Cms\FileBlueprint;

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files/(:any)/blueprint',
    'action'  => function ($path, $filename) {

        $file      = $this->site()->file($path . '/' . $filename);
        $blueprint = new FileBlueprint($file);

        return $blueprint->toArray();

    }
];
