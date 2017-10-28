<?php

use Kirby\Cms\File;

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files',
    'method'  => ['POST', 'OPTIONS'],
    'action'  => function ($path) {

        $page       = $this->site()->find($path);
        $request    = $this->request();
        $attributes = json_decode($request->body()->get('attributes'), true);

        if ($request->is('OPTIONS')) {
            return true;
        }

        foreach ($request->files()->data() as $file) {
            File::create($page, $file['tmp_name'], $file['name'], (array)$attributes);
        }

        return true;

    }
];
