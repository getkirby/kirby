<?php

return [
    'pattern' => 'pages/(:all)/files',
    'method'  => ['POST', 'OPTIONS'],
    'action'  => function ($path) {

        $page    = $this->site()->find($path);
        $request = $this->request();

        if ($request->is('OPTIONS')) {
            return true;
        }

        foreach ($request->files()->data() as $file) {
            move_uploaded_file($file['tmp_name'], $page->root() . '/' . $file['name']);
        }

        return true;

    }
];
