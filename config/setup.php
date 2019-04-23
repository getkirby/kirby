<?php

/**
 * Constants
 */
define('DS', '/');

/**
 * Load files that can't be autoloaded
 */
require_once __DIR__ . '/helpers.php';

/**
 * Class aliases
 */
$aliases = [
    // cms classes
    'asset'      => 'Kirby\Cms\Asset',
    'collection' => 'Kirby\Cms\Collection',
    'dir'        => 'Kirby\Cms\Dir',
    'field'      => 'Kirby\Cms\Field',
    'file'       => 'Kirby\Cms\File',
    'files'      => 'Kirby\Cms\Files',
    'html'       => 'Kirby\Cms\Html',
    'kirby'      => 'Kirby\Cms\App',
    'page'       => 'Kirby\Cms\Page',
    'pages'      => 'Kirby\Cms\Pages',
    'pagination' => 'Kirby\Cms\Pagination',
    'r'          => 'Kirby\Cms\R',
    'response'   => 'Kirby\Cms\Response',
    's'          => 'Kirby\Cms\S',
    'site'       => 'Kirby\Cms\Site',
    'structure'  => 'Kirby\Cms\Structure',
    'url'        => 'Kirby\Cms\Url',
    'user'       => 'Kirby\Cms\User',
    'users'      => 'Kirby\Cms\Users',
    'visitor'    => 'Kirby\Cms\Visitor',

    // data handler
    'data'      => 'Kirby\Data\Data',
    'json'      => 'Kirby\Data\Json',
    'yaml'      => 'Kirby\Data\Yaml',

    // data classes
    'database'  => 'Kirby\Database\Database',
    'db'        => 'Kirby\Database\Db',

    // http classes
    'cookie'     => 'Kirby\Http\Cookie',
    'header'     => 'Kirby\Http\Header',
    'remote'     => 'Kirby\Http\Remote',
    'server'     => 'Kirby\Http\Server',

    // image classes
    'dimensions' => 'Kirby\Image\Dimensions',

    // toolkit classes
    'a'          => 'Kirby\Toolkit\A',
    'c'          => 'Kirby\Toolkit\Config',
    'config'     => 'Kirby\Toolkit\Config',
    'escape'     => 'Kirby\Toolkit\Escape',
    'f'          => 'Kirby\Toolkit\F',
    'i18n'       => 'Kirby\Toolkit\I18n',
    'mime'       => 'Kirby\Toolkit\Mime',
    'obj'        => 'Kirby\Toolkit\Obj',
    'str'        => 'Kirby\Toolkit\Str',
    'tpl'        => 'Kirby\Toolkit\Tpl',
    'v'          => 'Kirby\Toolkit\V',
    'xml'        => 'Kirby\Toolkit\Xml'
];

spl_autoload_register(function ($class) use ($aliases) {
    $class = strtolower($class);

    if (isset($aliases[$class]) === true) {
        class_alias($aliases[$class], $class);
    }
});

/**
 * Tests
 */
$testDir = dirname(__DIR__) . '/tests';

if (is_dir($testDir) === true) {
    spl_autoload_register(function ($className) use ($testDir) {
        $path = str_replace('Kirby\\', '', $className);
        $path = str_replace('\\', '/', $path);
        $file = $testDir . '/' . $path . '.php';

        if (file_exists($file)) {
            include $file;
        }
    });
}
