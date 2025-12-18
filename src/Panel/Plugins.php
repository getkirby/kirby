<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\F;
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
			// During plugin development, kirbyup adds an index.dev.js,
			// which Kirby will load instead of the regular index.js.
			$this->files[] = $plugin->root() . '/index.dev.js';
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

			if ($type === 'js') {
				// filter out all index.js files that shouldn't be loaded
				// because an index.dev.js exists
				if (F::exists(preg_replace('/\.js$/', '.dev.js', $file)) === true) {
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
