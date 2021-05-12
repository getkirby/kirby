<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\View;
use Throwable;

/**
 * The Panel class is only responsible to create
 * a working panel view with all the right URLs
 * and other panel options. The view template is
 * located in `kirby/views/panel.php`
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Panel
{

     /**
     * Generates an array with all assets
     * that need to be loaded for the panel (js, css, icons)
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
    public static function assets(App $kirby): array
    {

        $dev = $kirby->option('panel.dev', false);

        if ($dev) {
            $url = 'http://localhost:3000';
        } else {
            $url = $kirby->url('media') . '/panel/' . $kirby->versionHash();
        }

        // fetch all plugins
        $plugins = new Plugins();

        $assets = [
            'css' => [
                'index'   => $url . '/css/index.css',
                'plugins' => $plugins->url('css'),
                'custom'  => static::customCss($kirby),
            ],
            'icons' => [
                'apple-touch-icon' => [
                    'type' => 'image/png',
                    'url'  => $url . '/apple-touch-icon.png',
                ],
                'shortcut icon' => [
                    'type' => 'image/svg+xml',
                    'url'  => $url . '/favicon.svg',
                ],
                'alternate icon' => [
                    'type' => 'image/png',
                    'url'  => $url . '/favicon.png',
                ]
            ],
            'js' => [
                'vendor'       => $url . '/js/vendor.js',
                'pluginloader' => $url . '/js/plugins.js',
                'plugins'      => $plugins->url('js'),
                'custom'       => static::customJs($kirby),
                'index'        => $url . '/js/index.js',
            ]
        ];

        if ($dev) {
            $assets['js']['vite']   = $url . '/@vite/client';
            $assets['js']['index']  = $url . '/src/index.js';
            $assets['js']['vendor'] = null;
            $assets['css']['index'] = null;
        }

        // remove missing files
        $assets['css'] = array_filter($assets['css']);
        $assets['js']  = array_filter($assets['js']);

        return $assets;
    }

    /**
     * Check for a custom css file from the
     * config (panel.css)
     *
     * @param \Kirby\Cms\App $kirby
     * @return string|false
     */
    public static function customCss(App $kirby)
    {
        if ($css = $kirby->option('panel.css')) {
            $asset = asset($css);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return false;
    }

    /**
     * Check for a custom js file from the
     * config (panel.js)
     *
     * @param \Kirby\Cms\App $kirby
     * @return string|false
     */
    public static function customJs(App $kirby)
    {
        if ($js = $kirby->option('panel.js')) {
            $asset = asset($js);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return false;
    }

    /**
     * Load the SVG icon sprite
     * This will be injected in the
     * initial HTML document for the Panel
     *
     * @param \Kirby\Cms\App $kirby
     * @return string
     */
    public static function icons(App $kirby): string
    {
        return F::read($kirby->root('kirby') . '/panel/dist/img/icons.svg');
    }

    /**
     * Links all dist files in the media folder
     * and returns the link to the requested asset
     *
     * @param \Kirby\Cms\App $kirby
     * @return bool
     * @throws \Exception If Panel assets could not be moved to the public directory
     */
    public static function link(App $kirby): bool
    {
        $mediaRoot   = $kirby->root('media') . '/panel';
        $panelRoot   = $kirby->root('panel') . '/dist';
        $versionHash = $kirby->versionHash();
        $versionRoot = $mediaRoot . '/' . $versionHash;

        // check if the version already exists
        if (is_dir($versionRoot) === true) {
            return false;
        }

        // delete the panel folder and all previous versions
        Dir::remove($mediaRoot);

        // recreate the panel folder
        Dir::make($mediaRoot, true);

        // create a symlink to the dist folder
        if (Dir::copy($panelRoot, $versionRoot) !== true) {
            throw new Exception('Panel assets could not be linked');
        }

        return true;
    }

    /**
     * Renders the main panel view
     *
     * @param \Kirby\Cms\App $kirby
     * @param array $inertia
     * @return \Kirby\Http\Response
     */
    public static function render(App $kirby, array $inertia = [])
    {
        try {
            if (static::link($kirby) === true) {
                usleep(1);
                go($kirby->url('index') . '/' . $kirby->path());
            }
        } catch (Throwable $e) {
            die('The Panel assets cannot be installed properly. ' . $e->getMessage());
        }

        // get the uri object for the panel url
        $uri = new Uri($url = $kirby->url('panel'));

        // fetch all plugins
        $plugins = new Plugins();

        $view = new View($kirby->root('kirby') . '/views/panel.php', [
            'assets'   => static::assets($kirby),
            'icons'    => static::icons($kirby),
            'nonce'    => $kirby->nonce(),
            'inertia'  => $inertia,
            'panelUrl' => $uri->path()->toString(true) . '/',
        ]);

        return new Response($view->render());
    }

    /**
     * Router for the Panel views
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $path
     * @return \Kirby\Http\Response|false
     */
    public static function router(App $kirby, string $path = null)
    {
        if ($kirby->option('panel') === false) {
            return false;
        }

        // load all Panel routes
        $routes = (require $kirby->root('kirby') . '/config/panel.php')($kirby);

        // create a micro-router for the Panel
        $result = router($path, $kirby->request()->method(), $routes);

        // pass responses directly down to the Kirby router
        if (is_a($result, 'Kirby\Http\Response') === true) {
            return $result;
        }

        // interpret strings as errors
        if (is_string($result) === true) {
            return Inertia::error($result);
        }

        // only expect arrays from here on
        if (is_array($result) === false) {
            throw new InvalidArgumentException("Invalid Panel response");
        }

        $view = $result['view'] ?? 'site';

        if (is_string($view) === true) {
            $view = Inertia::view($view);
        } else {
            $view = Inertia::view($view['id'] ?? 'site', $view);
        }

        return Inertia::render($result['component'], [
            '$props' => $result['props'] ?? [],
            '$view'  => $view,
        ]);
    }
}
