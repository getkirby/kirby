<?php

namespace Kirby\Cms;

use Exception;
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
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Panel
{
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
     * @return \Kirby\Cms\Response
     */
    public static function render(App $kirby)
    {
        try {
            if (static::link($kirby) === true) {
                usleep(1);
                go($kirby->url('index') . '/' . $kirby->path());
            }
        } catch (Throwable $e) {
            die('The panel assets cannot be installed properly. Please check permissions of your media folder.');
        }

        // get the uri object for the panel url
        $uri = new Uri($url = $kirby->url('panel'));

        // fetch all plugins
        $plugins = new PanelPlugins();

        $view = new View($kirby->root('kirby') . '/views/panel.php', [
            'kirby'     => $kirby,
            'config'    => $kirby->option('panel'),
            'assetUrl'  => $kirby->url('media') . '/panel/' . $kirby->versionHash(),
            'customCss' => static::customCss($kirby),
            'icons'     => static::icons($kirby),
            'pluginCss' => $plugins->url('css'),
            'pluginJs'  => $plugins->url('js'),
            'panelUrl'  => $uri->path()->toString(true) . '/',
            'nonce'     => $kirby->nonce(),
            'options'   => [
                'url'         => $url,
                'site'        => $kirby->url('index'),
                'api'         => $kirby->url('api'),
                'csrf'        => $kirby->option('api.csrf') ?? csrf(),
                'translation' => 'en',
                'debug'       => $kirby->option('debug', false),
                'search'      => [
                    'limit' => $kirby->option('panel.search.limit') ?? 10
                ]
            ]
        ]);

        return new Response($view->render());
    }
}
