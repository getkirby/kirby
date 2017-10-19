<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'pages/(:all)/files/(:any)',
    'method'  => 'POST',
    'action'  => function ($path, $filename) {
        if ($file = $this->site()->find($path)->file($filename)) {
            $file = $file->update($this->input());
            return $this->output('file', $file);
        }
    }
];
