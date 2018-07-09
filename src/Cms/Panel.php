<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Response;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\View;
use Throwable;

/**
 * The Panel class is only responsible to create
 * a working panel view with all the right URLs
 * and other panel options. The view template is
 * located in `kirby/views/panel.php`
 */
class Panel
{

    /**
     * Links all dist files in the media folder
     * and returns the link to the requested asset
     *
     * @param App $kirby
     * @return bool
     */
    public static function link(App $kirby): bool
    {
        $mediaRoot   = $kirby->root('media') . '/panel';
        $panelRoot   = $kirby->root('panel') . '/dist';
        $versionHash = md5($kirby->version());
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
        if (Dir::copy($kirby->root('panel') . '/dist', $versionRoot) !== true) {
            throw new Exception('Panel assets could not be linked');
        }

        return true;
    }

    /**
     * Renders the main panel view
     *
     * @param App $kirby
     * @return Response
     */
    public static function render(App $kirby): Response
    {
        try {
            if (static::link($kirby) === true) {
                go($kirby->request()->url());
            }
        } catch (Throwable $e) {
            die('The panel assets cannot be installed properly. Please check permissions of your media folder.');
        }

        $view = new View($kirby->root('kirby') . '/views/panel.php', [
            'kirby'     => $kirby,
            'config'    => $kirby->option('panel'),
            'assetUrl'  => $kirby->url('media') . '/panel/' . md5($kirby->version()),
            'pluginCss' => $kirby->url('media') . '/plugins/index.css',
            'pluginJs'  => $kirby->url('media') . '/plugins/index.js',
            'icons'     => F::read($kirby->root('panel') . '/dist/img/icons.svg'),
            'panelUrl'  => $url = $kirby->url('panel'),
            'options'   => [
                'url'         => $url,
                'site'        => $kirby->url('index'),
                'api'         => $kirby->url('api'),
                'translation' => 'en',
                'debug'       => true
            ]
        ]);

        return new Response($view->render());
    }
}
