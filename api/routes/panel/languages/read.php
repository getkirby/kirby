<?php

use Kirby\Data\Data;

return [
    'pattern' => 'panel/languages/(:any)',
    'action'  => function ($locale) {

        $dir     = $this->app()->root('panel') . '/assets/languages/' . $locale;
        $package = $dir . '/package.json';
        $core    = $dir . '/core.json';

        if (is_dir($dir) === false || is_file($package) === false || is_file($core) === false) {
            return null;
        }

        $json            = Data::read($package);
        $json['strings'] = Data::read($core);
        $json['locale']  = $locale;

        return $this->output('panel/language', $json);

    }
];
