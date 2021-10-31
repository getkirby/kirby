<?php

namespace Kirby\Panel;

use Kirby\Exception\Exception;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * The Document is used by the View class to render
 * the full Panel HTML document in Fiber calls that
 * should not return just JSON objects
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Document
{
    /**
     * Generates an array with all assets
     * that need to be loaded for the panel (js, css, icons)
     *
     * @return array
     */
    public static function assets(): array
    {
        $kirby = kirby();
        $nonce = $kirby->nonce();

        // get the assets from the Vite dev server in dev mode;
        // dev mode = explicitly enabled in the config AND Vite is running
        $dev   = $kirby->option('panel.dev', false);
        $isDev = $dev !== false && is_file($kirby->roots()->panel() . '/.vite-running') === true;

        if ($isDev === true) {
            // vite on explicitly configured base URL or port 3000
            // of the current Kirby request
            if (is_string($dev) === true) {
                $url = $dev;
            } else {
                $url = rtrim($kirby->request()->url([
                    'port'   => 3000,
                    'path'   => null,
                    'params' => null,
                    'query'  => null
                ])->toString(), '/');
            }
        } else {
            // vite is not running, use production assets
            $url = $kirby->url('media') . '/panel/' . $kirby->versionHash();
        }

        // fetch all plugins
        $plugins = new Plugins();

        $assets = [
            'css' => [
                'index'   => $url . '/css/style.css',
                'plugins' => $plugins->url('css'),
                'custom'  => static::customCss(),
            ],
            'icons' => $kirby->option('panel.favicon', [
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
            ]),
            'js' => [
                'vendor'       => [
                    'nonce' => $nonce,
                    'src'   => $url . '/js/vendor.js',
                    'type'  => 'module'
                ],
                'pluginloader' => [
                    'nonce' => $nonce,
                    'src'   => $url . '/js/plugins.js',
                    'type'  => 'module'
                ],
                'plugins'      => [
                    'nonce' => $nonce,
                    'src'   => $plugins->url('js'),
                    'defer' => true
                ],
                'custom'       => [
                    'nonce' => $nonce,
                    'src'   => static::customJs(),
                    'type'  => 'module'
                ],
                'index'        => [
                    'nonce' => $nonce,
                    'src'   => $url . '/js/index.js',
                    'type'  => 'module'
                ],
            ]
        ];

        // during dev mode, add vite client and adapt
        // path to `index.js` - vendor and stylesheet
        // don't need to be loaded in dev mode
        if ($isDev === true) {
            $assets['js']['vite']   = [
                'nonce' => $nonce,
                'src'   => $url . '/@vite/client',
                'type'  => 'module'
            ];
            $assets['js']['index']  = [
                'nonce' => $nonce,
                'src'   => $url . '/src/index.js',
                'type'  => 'module'
            ];

            unset($assets['css']['index'], $assets['js']['vendor']);
        }

        // remove missing files
        $assets['css'] = array_filter($assets['css']);
        $assets['js']  = array_filter(
            $assets['js'],
            fn ($js) => empty($js['src']) === false
        );

        return $assets;
    }

    /**
     * Check for a custom css file from the
     * config (panel.css)
     *
     * @return string|null
     */
    public static function customCss(): ?string
    {
        if ($css = kirby()->option('panel.css')) {
            $asset = asset($css);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return null;
    }

    /**
     * Check for a custom js file from the
     * config (panel.js)
     *
     * @return string|null
     */
    public static function customJs(): ?string
    {
        if ($js = kirby()->option('panel.js')) {
            $asset = asset($js);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return null;
    }

    /**
     * Load the SVG icon sprite
     * This will be injected in the
     * initial HTML document for the Panel
     *
     * @return string
     */
    public static function icons(): string
    {
        return F::read(kirby()->root('kirby') . '/panel/dist/img/icons.svg');
    }

    /**
     * Links all dist files in the media folder
     * and returns the link to the requested asset
     *
     * @return bool
     * @throws \Kirby\Exception\Exception If Panel assets could not be moved to the public directory
     */
    public static function link(): bool
    {
        $kirby       = kirby();
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

        // copy assets to the dist folder
        if (Dir::copy($panelRoot, $versionRoot) !== true) {
            throw new Exception('Panel assets could not be linked');
        }

        return true;
    }

    /**
     * Renders the panel document
     *
     * @param array $fiber
     * @return \Kirby\Http\Response
     */
    public static function response(array $fiber)
    {
        $kirby = kirby();

        // Full HTML response
        // @codeCoverageIgnoreStart
        try {
            if (static::link() === true) {
                usleep(1);
                go($kirby->url('index') . '/' . $kirby->path());
            }
        } catch (Throwable $e) {
            die('The Panel assets cannot be installed properly. ' . $e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        // get the uri object for the panel url
        $uri = new Uri($url = $kirby->url('panel'));

        // proper response code
        $code = $fiber['$view']['code'] ?? 200;

        // load the main Panel view template
        $body = Tpl::load($kirby->root('kirby') . '/views/panel.php', [
            'assets'   => static::assets(),
            'icons'    => static::icons(),
            'nonce'    => $kirby->nonce(),
            'fiber'    => $fiber,
            'panelUrl' => $uri->path()->toString(true) . '/',
        ]);

        return new Response($body, 'text/html', $code);
    }
}
