<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;

/**
 * Plugin assets are automatically copied/linked
 * to the media folder, to make them publicly
 * available. This class handles the magic around that.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PluginAssets extends Collection
{
	/**
	 * Clean old/deprecated assets on every resolve
	 */
	public static function clean(string $pluginName): void
	{
		if ($plugin = App::instance()->plugin($pluginName)) {
			$media  = $plugin->mediaRoot();
			$assets = $plugin->assets();

			// get all media files
			$files = Dir::index($media, true);

			// get all active assets' paths from the plugin
			$active = $assets->values(
				function ($asset) {
					$path  = $asset->mediaHash() . '/' . $asset->path();
					$paths = [];
					$parts = explode('/', $path);

					// collect all path segments
					// (e.g. foo/, foo/bar/, foo/bar/baz.css) for the asset
					for ($i = 1, $max = count($parts); $i <= $max; $i++) {
						$paths[] = implode('/', array_slice($parts, 0, $i));

						// TODO: remove when media hash is enforced as mandatory
						$paths[] = implode('/', array_slice($parts, 1, $i));
					}

					return $paths;
				}
			);

			// flatten the array and remove duplicates
			$active = array_unique(array_merge(...array_values($active)));

			// get outdated media files by comparing all
			// files in the media folder against the set of asset paths
			$stale  = array_diff($files, $active);

			foreach ($stale as $file) {
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
	 * Filters assets collection by CSS files
	 */
	public function css(): static
	{
		return $this->filter(fn ($asset) => $asset->extension() === 'css');
	}

	/**
	 * Creates a new collection for the plugin's assets
	 * by considering the plugin's `asset` extension
	 * (and `assets` directory as fallback)
	 */
	public static function factory(Plugin $plugin): static
	{
		// get assets defined in the plugin extension
		if ($assets = $plugin->extends()['assets'] ?? null) {
			if ($assets instanceof Closure) {
				$assets = $assets();
			}

			// normalize array: use relative path as
			// key when no key is defined
			foreach ($assets as $key => $root) {
				if (is_int($key) === true) {
					unset($assets[$key]);
					$path = Str::after($root, $plugin->root() . '/');
					$assets[$path] = $root;
				}
			}
		}

		// fallback: if no assets are defined in the plugin extension,
		// use all files in the plugin's `assets` directory
		if ($assets === null) {
			$assets = [];
			$root   = $plugin->root() . '/assets';

			foreach (Dir::index($root, true) as $path) {
				if (is_file($root . '/' . $path) === true) {
					$assets[$path] = $root . '/' . $path;
				}
			}
		}

		$collection = new static([], $plugin);

		foreach ($assets as $path => $root) {
			$collection->data[$path] = new PluginAsset($path, $root, $plugin);
		}

		return $collection;
	}

	/**
	 * Filters assets collection by JavaScript files
	 */
	public function js(): static
	{
		return $this->filter(fn ($asset) => $asset->extension() === 'js');
	}

	public function plugin(): Plugin
	{
		return $this->parent;
	}

	/**
	 * Create a symlink for a plugin asset and
	 * return the public URL
	 */
	public static function resolve(
		string $pluginName,
		string $hash,
		string $path
	): Response|null {
		if ($plugin = App::instance()->plugin($pluginName)) {
			// do some spring cleaning for older files
			static::clean($pluginName);

			// @codeCoverageIgnoreStart
			// TODO: deprecated media URL without hash
			if (empty($hash) === true) {
				$asset = $plugin->asset($path);
				$asset->publishAt($path);
				return Response::file($asset->root());
			}

			// TODO: deprecated media URL with hash (but path)
			if ($asset = $plugin->asset($hash . '/' . $path)) {
				$asset->publishAt($hash . '/' . $path);
				return Response::file($asset->root());
			}
			// @codeCoverageIgnoreEnd

			if ($asset = $plugin->asset($path)) {
				if ($asset->mediaHash() === $hash) {
					// create a symlink if possible
					$asset->publish();

					// return the file response
					return Response::file($asset->root());
				}
			}
		}

		return null;
	}
}
