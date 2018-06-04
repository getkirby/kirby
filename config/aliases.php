<?php

$aliases = [

    // cms classes
    'dir'       => 'Kirby\Cms\Dir',
    'file'      => 'Kirby\Cms\File',
    'html'      => 'Kirby\Cms\Html',
    'kirby'     => 'Kirby\Cms\App',
    'page'      => 'Kirby\Cms\Page',
    'response'  => 'Kirby\Cms\Response',
    'site'      => 'Kirby\Cms\Site',
    'structure' => 'Kirby\Cms\Structure',
    'url'       => 'Kirby\Cms\Url',

    // data handler
    'data'      => 'Kirby\Data\Data',
    'json'      => 'Kirby\Data\Json',
    'yaml'      => 'Kirby\Data\Yaml',

    // toolkit classes
    'a'         => 'Kirby\Toolkit\A',
    'f'         => 'Kirby\Toolkit\F',
    'i18n'      => 'Kirby\Toolkit\I18n',
    'str'       => 'Kirby\Toolkit\Str',
    'v'         => 'Kirby\Toolkit\V',

];

spl_autoload_register(function ($class) use ($aliases) {

    $class = strtolower($class);

    if (isset($aliases[$class]) === true) {
        class_alias($aliases[$class], $class);
    }

});
