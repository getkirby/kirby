<?php

use Kirby\Data\Data;

return [
    'pattern' => 'panel/languages/(:any)',
    'action'  => function ($locale) {

        $file = $this->app()->root('panel') . '/assets/languages/' . $locale . '/package.json';
        $json = Data::read($file);
        $json['locale'] = $locale;

        return $this->output('language', $json);

    }
];
