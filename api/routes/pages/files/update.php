<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'pages/(:all)/files/(:any)',
    'method'  => ['POST', 'OPTIONS'],
    'action'  => function ($path, $filename) {

        $request = $this->request();

        if ($request->is('OPTIONS')) {
            return true;
        }

        if ($file = $this->site()->file($path . '/' . $filename)) {

            foreach ($request->files()->data() as $upload) {
                move_uploaded_file($upload['tmp_name'], $file->root());
            }

            $file = $file->update($this->input());
            return $this->output('file', $file);

        }
    }
];
