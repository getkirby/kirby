<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * The Plugins class takes care of collecting
 * js and css plugin files for the panel and caches
 * them in the media folder
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Plugins
{
	/**
	 * Cache of all collected plugin files
	 */
	public array|null $files = null;

	/**
	 * Collects and returns the plugin files for all plugins
	 */
	public function files(): array
	{
		if ($this->files !== null) {
			return $this->files;
		}

		$this->files = [];

		foreach (App::instance()->plugins() as $plugin) {
			$this->files[] = $plugin->root() . '/index.css';
			$this->files[] = $plugin->root() . '/index.js';
			// During plugin development, kirbyup adds an index.dev.mjs as entry point, which
			// Kirby will load instead of the regular index.js. Since kirbyup is based on Vite,
			// it can't use the standard index.js as entry for its development server:
			// Vite requires an entry of type module so it can use JavaScript imports,
			// but Kirbyup needs index.js to load as a regular script, synchronously.
			$this->files[] = $plugin->root() . '/index.dev.mjs';
		}

		return $this->files;
	}

	/**
	 * Returns the last modification
	 * of the collected plugin files
	 */
	public function modified(): int
	{
		$files    = $this->files();
		$modified = [0];

		foreach ($files as $file) {
			$modified[] = F::modified($file);
		}

		return max($modified);
	}

	/**
	 * Read the files from all plugins and concatenate them
	 */
	public function read(string $type): string
	{
		$dist = [];

		foreach ($this->files() as $file) {
			// filter out files with a different type
			if (F::extension($file) !== $type) {
				continue;
			}

			// filter out empty files and files that don't exist
			$content = F::read($file);
			if (!$content) {
				continue;
			}

			if ($type === 'mjs') {
				// index.dev.mjs files are turned into data URIs so they
				// can be imported without having to copy them to /media
				// (avoids having to clean the files from /media again)
				$content = F::uri($file);
			}

			if ($type === 'js') {
				// filter out all index.js files that shouldn't be loaded
				// because an index.dev.mjs exists
				if (F::exists(preg_replace('/\.js$/', '.dev.mjs', $file)) === true) {
					continue;
				}

				$content = trim($content);

				// make sure that each plugin is ended correctly
				if (Str::endsWith($content, ';') === false) {
					$content .= ';';
				}
			}

			$dist[] = $content;
		}

		if ($type === 'mjs') {
			// if no index.dev.mjs modules exist, we MUST return an empty string instead
			// of loading an empty array; this is because the module loader code uses
			// top level await, which is not compatible with Kirby's minimum browser
			// version requirements and therefore must not appear in a default setup
			if ($dist === []) {
				return '';
			}

			$modules = Json::encode($dist);
			$modulePromise = "Promise.all($modules.map(url => import(url)))";
			return "try { await $modulePromise } catch (e) { console.error(e) }" . PHP_EOL;
		}

		return implode(PHP_EOL . PHP_EOL, $dist);
	}

	/**
	 * Absolute url to the cache file
	 * This is used by the panel to link the plugins
	 */
	public function url(string $type): string
	{
		return App::instance()->url('media') . '/plugins/index.' . $type . '?' . $this->modified();
	}
}
