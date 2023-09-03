<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

/**
 * Plugin assets are automatically copied/linked
 * to the media folder, to make them publicly
 * available. This class handles the magic around that.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PluginAssets
{
	/**
	 * Clean old/deprecated assets on every resolve
	 */
	public static function clean(string $pluginName): void
	{
		if ($plugin = App::instance()->plugin($pluginName)) {
			$media  = $plugin->mediaRoot();
			$assets = $plugin->assets();
			$files  = Dir::index($media, true);
			$files  = array_diff($files, array_keys($assets));

			foreach ($files as $file) {
				$root = $media . '/' . $file;

				if (is_file($root) === true) {
					F::remove($root);
				} else {
					Dir::remove($root);
				}
			}
		}
	}

	/**
	 * Create a symlink for a plugin asset and
	 * return the public URL
	 */
	public static function resolve(
		string $pluginName,
		string $path
	): Response|null {
		if ($plugin = App::instance()->plugin($pluginName)) {
			if (
				($asset = $plugin->asset($path)) &&
				F::exists($asset, $plugin->root()) === true
			) {
				// do some spring cleaning for older files
				static::clean($pluginName);

				$target = $plugin->mediaRoot() . '/' . $path;

				// create a symlink if possible
				F::link($asset, $target, 'symlink');

				// return the file response
				return Response::file($asset);
			}
		}

		return null;
	}
}
