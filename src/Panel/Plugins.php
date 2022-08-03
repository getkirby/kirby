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
	 *
	 * @var array
	 */
	public $files;

	/**
	 * Collects and returns the plugin files for all plugins
	 *
	 * @return array
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
			// used by kirbyup during plugin development
			$this->files[] = $plugin->root() . '/_index.mjs';
		}

		return $this->files;
	}

	/**
	 * Returns the last modification
	 * of the collected plugin files
	 *
	 * @return int
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
	 *
	 * @param string $type
	 * @return string
	 */
	public function read(string $type): string
	{
		$dist = [];

		$files = $this->files();

		// filter out index.js files that have an _index.mjs counterpart (which takes precedence)
		if ($type === 'js') {
			$files = A::filter(
				$files,
				fn ($f) => !(
					Str::endsWith($f, 'index.js') && F::exists(preg_replace('/index\.js$/', '_index.mjs', $f))
				)
			);
		}

		foreach ($files as $file) {
			if (F::extension($file) === $type) {
				if ($content = F::read($file)) {
					if ($type === 'mjs') {
						$content = F::uri($file);
					}

					if ($type === 'js') {
						$content = trim($content);

						// make sure that each plugin is ended correctly
						if (Str::endsWith($content, ';') === false) {
							$content .= ';';
						}
					}

					$dist[] = $content;
				}
			}
		}

		if ($type === 'mjs') {
			if (empty($dist)) {
				return '';
			}

			$modules = Json::encode($dist);
			$modulePromise = "Promise.all($modules.map(url => import(url)))";
			return "try { await $modulePromise } catch (e) { console.error(e) }";
		}

		return implode(PHP_EOL . PHP_EOL, $dist);
	}

	/**
	 * Absolute url to the cache file
	 * This is used by the panel to link the plugins
	 *
	 * @param string $type
	 * @return string
	 */
	public function url(string $type): string
	{
		return App::instance()->url('media') . '/plugins/index.' . $type . '?' . $this->modified();
	}
}
