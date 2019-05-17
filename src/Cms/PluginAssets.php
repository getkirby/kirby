<?php

namespace Kirby\Cms;

use Kirby\Http\Response;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * Plugin assets are automatically copied/linked
 * to the media folder, to make them publicly
 * available. This class handles the magic around that.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class PluginAssets
{
    /**
     * Clean old/deprecated assets on every resolve
     *
     * @param string $pluginName
     * @return void
     */
    public static function clean(string $pluginName): void
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
     * @return Kirby\Cms\Response|null
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

                // create the plugin directory first
                Dir::make($plugin->mediaRoot(), true);

                if (F::link($source, $target, 'symlink') === true) {
                    return Response::redirect($url);
                }

                return Response::file($source);
            }
        }

        return null;
    }
}
