<?php

use Kirby\Cms\Input;

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)/files/(:any)',
    'method'  => ['POST', 'OPTIONS'],
    'action'  => function ($path, $filename) {

        $request = $this->request();

        if ($request->is('OPTIONS')) {
            return true;
        }

        if ($file = $this->site()->file($path . '/' . $filename)) {

            foreach ($request->files()->data() as $upload) {
                $file->replace($upload['tmp_name']);
            }

            $input = $this->input();
            $data  = (new Input($file, $input))->toArray();

            return $this->output('file', $file->update($data));

        }
    }
];
