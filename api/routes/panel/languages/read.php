<?php

use Kirby\Data\Data;

return [
    'pattern' => 'panel/languages/(:any)',
    'action'  => function ($locale) {

        $dir             = $this->app()->root('panel') . '/assets/languages/' . $locale;
        $json            = Data::read($dir . '/package.json');
        $json['locale']  = $locale;
        $json['strings'] = Data::read($dir . '/core.json');

        return $this->output('panel/language', $json);

    }
];
