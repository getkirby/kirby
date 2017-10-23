<?php

use Kirby\Cms\Assets\PageAssets;

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

                $assets = new PageAssets($this->app()->root('files'), $file->page());
                $assets->delete($filename);
            }

            $file = $file->update($this->input());
            return $this->output('file', $file);

        }
    }
];
