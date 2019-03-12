<?php

namespace Kirby\Cms;

use Kirby\Http\Response;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * Plugin assets are automatically copied/linked
 * to the media folder, to make them publicly
 * available. This class handles the magic around that.
 */
class PluginAssets
{

    /**
     * Concatenate all plugin js and css files into
     * a single file and copy them to /media/plugins/index.css or /media/plugins/index.js
     *
     * @param string $extension
     * @return string
     */
    public static function index(string $extension): string
    {
        $kirby    = App::instance();
        $cache    = $kirby->root('media') . '/plugins/.index.' . $extension;
        $build    = false;
        $modified = [0];
        $assets   = [];

        foreach ($kirby->plugins() as $plugin) {
            $file = $plugin->root() . '/index.' . $extension;

            if (file_exists($file) === true) {
                $assets[]   = $file;
                $modified[] = F::modified($file);
            }
        }

        if (empty($assets)) {
            return false;
        }

        if (file_exists($cache) === false || filemtime($cache) < max($modified)) {
            $dist = [];
            foreach ($assets as $asset) {
                $dist[] = file_get_contents($asset);
            }
            $dist = implode(PHP_EOL, $dist);
            F::write($cache, $dist);
        } else {
            $dist = file_get_contents($cache);
        }

        return $dist;
    }

    /**
     * Clean old/deprecated assets on every resolve
     *
     * @param string $pluginName
     * @return void
     */
    public static function clean(string $pluginName)
    {
        if ($plugin = App::instance()->plugin($pluginName)) {
            $root   = $plugin->root() . '/assets';
            $media  = $plugin->mediaRoot();
            $assets = Dir::index($media, true);

            foreach ($assets as $asset) {
                $original = $root . '/' . $asset;

                if (file_exists($original) === false) {
                    $assetRoot = $media . '/' . $asset;

                    if (is_file($assetRoot) === true) {
                        F::remove($assetRoot);
                    } else {
                        Dir::remove($assetRoot);
                    }
                }
            }
        }
    }

    /**
     * Create a symlink for a plugin asset and
     * return the public URL
     *
     * @param string $pluginName
     * @param string $filename
     * @return string
     */
    public static function resolve(string $pluginName, string $filename)
    {
        if ($plugin = App::instance()->plugin($pluginName)) {
            $source = $plugin->root() . '/assets/' . $filename;

            if (F::exists($source, $plugin->root()) === true) {
                // do some spring cleaning for older files
                static::clean($pluginName);

                $target = $plugin->mediaRoot() . '/' . $filename;
                $url    = $plugin->mediaUrl() . '/' . $filename;

                if (F::link($source, $target, 'symlink') === true) {
                    return Response::redirect($url);
                }

                return Response::file($source);
            }
        }

        return null;
    }
}
