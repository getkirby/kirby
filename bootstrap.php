<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/extensions/helpers.php';
require __DIR__ . '/extensions/methods.php';

use Kirby\Cms\App as Kirby;
use Kirby\Http\Request;
use Kirby\Http\Server;
use Kirby\Image\Darkroom\GdLib as Darkroom;
use Kirby\Text\Markdown;
use Kirby\Text\Smartypants;
use Kirby\Text\Tags;
use Kirby\Toolkit\Url;

/**
 * App configuration
 */
$app = new Kirby([
    'url' => [
        '/'        => $url = Url::index(),
        'files'    => $url . '/files',
        'accounts' => $url . '/site/accounts'
    ],
    'root' => [
        '/'           => $root = dirname(__DIR__),
        'kirby'       => __DIR__,
        'files'       => $root . '/files',
        'content'     => $root . '/content',
        'controllers' => $root . '/site/controllers',
        'accounts'    => $root . '/site/accounts',
        'snippets'    => $root . '/site/snippets',
        'templates'   => $root . '/site/templates',
        'blueprints'  => $root . '/site/blueprints',
        'panel'       => $root . '/panel'
    ],
    'request'     => $request = new Request(),
    'server'      => $server  = new Server(),
    'smartypants' => new Smartypants(),
    'kirbytext'   => new Tags(),
    'markdown'    => new Markdown(['breaks' => true]),
    'darkroom'    => new Darkroom(['quality' => 80]),
    'users'       => require __DIR__ . '/config/users.php',
    'schema'      => require __DIR__ . '/config/schema.php',
    'site'        => require __DIR__ . '/config/site.php',
    'path'        => require __DIR__ . '/config/path.php',
    'routes'      => require __DIR__ . '/config/routes.php',
    'router'      => require __DIR__ . '/config/router.php',
    'system'      => require __DIR__ . '/config/system.php',
]);

echo $app->response();
