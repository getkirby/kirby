<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Plugin\Plugin;
use Kirby\Toolkit\A;

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
		return $this->urls('css');
	}

	/**
	 * Whether the given plugin, or any plugin if none is given,
	 * is in development, i.e. kirbyup added an `index.dev.js`
	 * @since 6.0.0
	 */
	public function isDev(Plugin|null $plugin = null): bool
	{
		if ($plugin === null) {
			return A::some(
				App::instance()->plugins(),
				fn (Plugin $plugin) => $this->isDev($plugin)
			);
		}

		return F::size($plugin->root() . '/index.dev.js') > 0;
	}

	/**
	 * Returns the URLs of all plugin scripts,
	 * in the order the plugins are loaded
	 * @since 6.0.0
	 */
	public function js(): array
	{
		return $this->urls('js');
	}

	/**
	 * Returns the stylesheet or script of a plugin
	 * as response; its URL path contains the file hash,
	 * so browsers and CDNs can cache it for good
	 * @since 6.0.0
	 *
	 * @param string $extension `css`, `js`, `dev.css` or `dev.js`
	 */
	public static function resolve(
		string $plugin,
		string $hash,
		string $extension
	): Response|null {
		$plugin = App::instance()->plugin($plugin);

		if ($plugin === null) {
			return null;
		}

		$root = $plugin->root() . '/index.' . $extension;

		// only serve the file the URL was created for,
		// so no cache stores another file under that URL
		if (is_file($root) === false || hash_file('xxh3', $root) !== $hash) {
			return null;
		}

		return Response::file($root, [
			'headers' => [
				'Cache-Control' => 'public, max-age=31536000, immutable'
			]
		]);
	}

	/**
	 * Returns the URL of the non-empty `index.css` or `index.js`
	 * of each plugin, or of its `index.dev.*` counterpart
	 * while the plugin is in development
	 * @since 6.0.0
	 *
	 * @param string $type `css` or `js`
	 */
	protected function urls(string $type): array
	{
		$urls = [];

		foreach (App::instance()->plugins() as $plugin) {
			$extension = $this->isDev($plugin) === true ? 'dev.' . $type : $type;
			$root      = $plugin->root() . '/index.' . $extension;

			if (F::size($root) > 0) {
				$urls[] = $plugin->mediaUrl() . '.' . hash_file('xxh3', $root) . '.' . $extension;
			}
		}

		return $urls;
	}
}
