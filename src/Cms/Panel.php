<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Response;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\View;

class Panel
{

    /**
     * Links all dist files in the media folder
     * and returns the link to the requested asset
     *
     * @return string
     */
    public static function link(App $kirby, string $path): string
    {
        $mediaRoot   = $kirby->root('media') . '/panel';
        $panelRoot   = $kirby->root('panel') . '/dist';
        $fileRoot    = $panelRoot . '/' . $path;
        $versionHash = md5($kirby->version());

        // delete the panel folder and all previous versions
        Dir::remove($mediaRoot);

        // check if the requested file exists at all
        if (F::exists($fileRoot, $panelRoot) === false) {
            throw new Exception('The file does not exist');
        }

        // create a symlink to the dist folder
        if (Dir::link($kirby->root('panel') . '/dist', $mediaRoot . '/' . $versionHash, 'symlink') !== true) {
            throw new Exception('Panel assets could not be linked');
        }

        // redirect to the newly created file
        return $kirby->url('media') . '/panel/' . $versionHash . '/' . $path;
    }

    /**
     * Renders the main panel view
     *
     * @param App $kirby
     * @return Response
     */
    public static function render(App $kirby): Response
    {
        $view = new View($kirby->root('kirby') . '/views/panel.php', [
            'kirby'     => $kirby,
            'assetUrl'  => $kirby->url('media') . '/panel/' . md5($kirby->version()),
            'pluginCss' => $kirby->url('media') . '/plugins/index.css',
            'pluginJs'  => $kirby->url('media') . '/plugins/index.js',
            'icons'     => F::read($kirby->root('panel') . '/dist/img/icons.svg'),
            'panelUrl'  => $kirby->url('panel'),
            'options'   => [
                'url'         => $kirby->url('panel'),
                'site'        => $kirby->url('index'),
                'api'         => $kirby->url('api'),
                'translation' => 'en',
                'debug'       => true
            ]
        ]);

        return new Response($view->render());
    }

}
