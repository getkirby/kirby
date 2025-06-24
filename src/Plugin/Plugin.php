<?php

namespace Kirby\Plugin;

use Closure;
use Composer\InstalledVersions;
use Kirby\Cms\App;
use Kirby\Cms\Helpers;
use Kirby\Cms\System\UpdateStatus;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Throwable;

/**
 * Represents a Plugin and handles parsing of
 * the composer.json. It also creates the prefix
 * and media url for the plugin.
 *
 * @package   Kirby Plugin
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Plugin
{
	protected Assets $assets;
	protected License|Closure|array|string $license;
	protected UpdateStatus|null $updateStatus = null;

	/**
	 * @param string $name Plugin name within Kirby (`vendor/plugin`)
	 * @param array $extends Associative array of plugin extensions
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the plugin name has an invalid format
	 */
	public function __construct(
		protected string $name,
		protected array $extends = [],
		protected array $info = [],
		Closure|string|array|null $license = null,
		protected string|null $root = null,
		protected string|null $version = null,
	) {
		static::validateName($name);

		// TODO: Remove in v7
		if ($root = $extends['root'] ?? null) {
			Helpers::deprecated('Plugin "' . $name . '": Passing the `root` inside the `extends` array has been deprecated. Pass it directly as named argument `root`.', 'plugin-extends-root');
			$this->root ??= $root;
			unset($this->extends['root']);
		}

		$this->root ??= dirname(debug_backtrace()[0]['file']);

		// TODO: Remove in v7
		if ($info = $extends['info'] ?? null) {
			Helpers::deprecated('Plugin "' . $name . '": Passing an `info` array inside the `extends` array has been deprecated. Pass the individual entries directly as named `info` argument.', 'plugin-extends-root');

			if (empty($info) === false && is_array($info) === true) {
				$this->info = [...$info, ...$this->info];
			}

			unset($this->extends['info']);
		}

		// read composer.json and use as info fallback
		$info          = Data::read($this->manifest(), fail: false);
		$this->info    = [...$info, ...$this->info];
		$this->license = $license ?? $this->info['license'] ?? '-';
	}

	/**
	 * Allows access to any composer.json field by method call
	 */
	public function __call(string $key, array|null $arguments = null): mixed
	{
		return $this->info()[$key] ?? null;
	}

	/**
	 * Returns the plugin asset object for a specific asset
	 */
	public function asset(string $path): Asset|null
	{
		return $this->assets()->get($path);
	}

	/**
	 * Returns the plugin assets collection
	 */
	public function assets(): Assets
	{
		return $this->assets ??= Assets::factory($this);
	}

	/**
	 * Returns the array with author information
	 * from the composer.json file
	 */
	public function authors(): array
	{
		return $this->info()['authors'] ?? [];
	}

	/**
	 * Returns a comma-separated list with all author names
	 */
	public function authorsNames(): string
	{
		$names = [];

		foreach ($this->authors() as $author) {
			$names[] = $author['name'] ?? null;
		}

		return implode(', ', array_filter($names));
	}

	/**
	 * Returns the associative array of extensions the plugin bundles
	 */
	public function extends(): array
	{
		return $this->extends;
	}

	/**
	 * Returns the unique ID for the plugin
	 * (alias for the plugin name)
	 */
	public function id(): string
	{
		return $this->name();
	}

	/**
	 * Returns the info data (from composer.json)
	 */
	public function info(): array
	{
		return $this->info;
	}

	/**
	 * Current $kirby instance
	 */
	public function kirby(): App
	{
		return App::instance();
	}

	/**
	 * Returns the link to the plugin homepage
	 */
	public function link(): string|null
	{
		$info     = $this->info();
		$homepage = $info['homepage'] ?? null;
		$docs     = $info['support']['docs'] ?? null;
		$source   = $info['support']['source'] ?? null;

		$link = $homepage ?? $docs ?? $source;

		return V::url($link) ? $link : null;
	}

	/**
	 * Returns the license object
	 */
	public function license(): License
	{
		// resolve license info from Closure, array or string
		return License::from(
			plugin: $this,
			license: $this->license
		);
	}

	/**
	 * Returns the path to the plugin's composer.json
	 */
	public function manifest(): string
	{
		return $this->root() . '/composer.json';
	}

	/**
	 * Returns the root where plugin assets are copied to
	 */
	public function mediaRoot(): string
	{
		return $this->kirby()->root('media') . '/plugins/' . $this->name();
	}

	/**
	 * Returns the base URL for plugin assets
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/plugins/' . $this->name();
	}

	/**
	 * Returns the plugin name (`vendor/plugin`)
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Returns a Kirby option value for this plugin
	 */
	public function option(string $key)
	{
		return $this->kirby()->option($this->prefix() . '.' . $key);
	}

	/**
	 * Returns the option prefix (`vendor.plugin`)
	 */
	public function prefix(): string
	{
		return str_replace('/', '.', $this->name());
	}

	/**
	 * Returns the root where the plugin files are stored
	 */
	public function root(): string
	{
		return $this->root;
	}

	/**
	 * Returns all available plugin metadata
	 */
	public function toArray(): array
	{
		return [
			'authors'     => $this->authors(),
			'description' => $this->description(),
			'name'        => $this->name(),
			'license'     => $this->license()->toArray(),
			'link'        => $this->link(),
			'root'        => $this->root(),
			'version'     => $this->version()
		];
	}

	/**
	 * Returns the update status object unless the
	 * update check has been disabled for the plugin
	 * @since 3.8.0
	 *
	 * @param array|null $data Custom override for the getkirby.com update data
	 */
	public function updateStatus(array|null $data = null): UpdateStatus|null
	{
		if ($this->updateStatus !== null) {
			return $this->updateStatus;
		}

		$kirby  = $this->kirby();
		$option = $kirby->option('updates.plugins');

		// specific configuration per plugin
		if (is_array($option) === true) {
			// filter all option values by glob match
			$option = A::filter(
				$option,
				fn ($value, $key) => fnmatch($key, $this->name()) === true
			);

			// sort the matches by key length (with longest key first)
			$keys = array_map('strlen', array_keys($option));
			array_multisort($keys, SORT_DESC, $option);

			if ($option !== []) {
				// use the first and therefore longest key (= most specific match)
				$option = reset($option);
			} else {
				// fallback to the default option value
				$option = true;
			}
		}

		$option ??= $kirby->option('updates') ?? true;

		if ($option !== true) {
			return null;
		}

		return $this->updateStatus = new UpdateStatus($this, false, $data);
	}

	/**
	 * Checks if the name follows the required pattern
	 * and throws an exception if not
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function validateName(string $name): void
	{
		if (preg_match('!^[a-z0-9-]+\/[a-z0-9-]+$!i', $name) !== 1) {
			throw new InvalidArgumentException(
				message: 'The plugin name must follow the format "a-z0-9-/a-z0-9-"'
			);
		}
	}

	/**
	 * Returns the normalized version number
	 * from the composer.json file
	 */
	public function version(): string|null
	{
		$name = $this->info()['name'] ?? null;

		try {
			// try to get version from "vendor/composer/installed.php",
			// this is the most reliable source for the version
			$version = InstalledVersions::getPrettyVersion($name);
		} catch (Throwable) {
			$version = null;
		}

		// fallback to the version provided in the plugin's index.php: as named
		// argument, entry in the info array or from the composer.json file
		$version ??= $this->version ?? $this->info()['version'] ?? null;

		if (
			is_string($version) !== true ||
			$version === '' ||
			Str::endsWith($version, '+no-version-set')
		) {
			return null;
		}

		// normalize the version number to be without leading `v`
		$version = ltrim($version, 'vV');

		// ensure that the version number now starts with a digit
		if (preg_match('/^[0-9]/', $version) !== 1) {
			return null;
		}

		return $version;
	}
}
