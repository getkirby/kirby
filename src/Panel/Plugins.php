<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

/**
 * The Plugins class collects the Panel files of all
 * plugins: each stylesheet and script is loaded on its own
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Plugins
{
	/**
	 * Returns the URLs of all plugin stylesheets,
	 * in the order the plugins are loaded
	 * @since 6.0.0
	 */
	public function css(): array
	{
		return $this->urls(['css']);
	}

	/**
	 * Returns the URLs of all plugin scripts,
	 * in the order the plugins are loaded
	 * @since 6.0.0
	 */
	public function js(): array
	{
		// during plugin development, kirbyup adds an index.dev.js,
		// which Kirby will load instead of the regular index.js
		return $this->urls(['dev.js', 'js']);
	}

	/**
	 * Returns the stylesheet or script of a plugin
	 * as response; its URL changes with the file,
	 * so browsers can cache it for good
	 * @since 6.0.0
	 *
	 * @param string $extension `css`, `js` or `dev.js`
	 */
	public static function resolve(
		string $plugin,
		string $extension
	): Response|null {
		$plugin = App::instance()->plugin($plugin);

		if ($plugin === null) {
			return null;
		}

		$root = $plugin->root() . '/index.' . $extension;

		if (is_file($root) === false) {
			return null;
		}

		return Response::file($root, [
			'headers' => [
				'Cache-Control' => 'public, max-age=31536000, immutable'
			]
		]);
	}

	/**
	 * Returns the URL of the first non-empty `index.*` file
	 * of each plugin, trying the extensions in the given order
	 * @since 6.0.0
	 */
	protected function urls(array $extensions): array
	{
		$urls = [];

		foreach (App::instance()->plugins() as $plugin) {
			foreach ($extensions as $extension) {
				$root = $plugin->root() . '/index.' . $extension;

				if (F::size($root) > 0) {
					$urls[] = $plugin->mediaUrl() . '.' . $extension . '?' . F::modified($root);
					break;
				}
			}
		}

		return $urls;
	}
}
