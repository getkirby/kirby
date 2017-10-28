<?php

use Kirby\Cms\File;

return [
    'auth'    => true,
    'pattern' => 'site/files',
    'method'  => ['POST', 'OPTIONS'],
    'action'  => function ($path) {

        $request = $this->request();

        if ($request->is('OPTIONS')) {
            return true;
        }

        foreach ($request->files()->data() as $file) {
            File::create(null, $file['tmp_name'], $file['name']);
        }

        return true;

    }
];
