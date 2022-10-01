<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Cms\System\UpdateStatus;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\V;

/**
 * Represents a Plugin and handles parsing of
 * the composer.json. It also creates the prefix
 * and media url for the plugin.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Plugin extends Model
{
	protected array $extends;
	protected string $name;
	protected string $root;

	// caches
	protected array|null $info = null;
	protected UpdateStatus|null $updateStatus = null;

	/**
	 * Allows access to any composer.json field by method call
	 */
	public function __call(string $key, array $arguments = null)
	{
		return $this->info()[$key] ?? null;
	}

	/**
	 * @param string $name Plugin name within Kirby (`vendor/plugin`)
	 * @param array $extends Associative array of plugin extensions
	 */
	public function __construct(string $name, array $extends = [])
	{
		$this->setName($name);
		$this->extends = $extends;
		$this->root    = $extends['root'] ?? dirname(debug_backtrace()[0]['file']);
		$this->info    = empty($extends['info']) === false && is_array($extends['info']) ? $extends['info'] : null;

		unset($this->extends['root'], $this->extends['info']);
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
	 * Returns the raw data from composer.json
	 */
	public function info(): array
	{
		if (is_array($this->info) === true) {
			return $this->info;
		}

		try {
			$info = Data::read($this->manifest());
		} catch (Exception) {
			// there is no manifest file or it is invalid
			$info = [];
		}

		return $this->info = $info;
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
		return App::instance()->root('media') . '/plugins/' . $this->name();
	}

	/**
	 * Returns the base URL for plugin assets
	 */
	public function mediaUrl(): string
	{
		return App::instance()->url('media') . '/plugins/' . $this->name();
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
	 * Validates and sets the plugin name
	 *
	 * @return $this
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the plugin name has an invalid format
	 */
	protected function setName(string $name): static
	{
		if (preg_match('!^[a-z0-9-]+\/[a-z0-9-]+$!i', $name) !== 1) {
			throw new InvalidArgumentException('The plugin name must follow the format "a-z0-9-/a-z0-9-"');
		}

		$this->name = $name;
		return $this;
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
			'license'     => $this->license(),
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

			if (count($option) > 0) {
				// use the first and therefore longest key (= most specific match)
				$option = reset($option);
			} else {
				// fallback to the default option value
				$option = true;
			}
		}

		if ($option === null) {
			$option = $kirby->option('updates') ?? true;
		}

		if ($option !== true) {
			return null;
		}

		return $this->updateStatus = new UpdateStatus($this, false, $data);
	}

	/**
	 * Returns the normalized version number
	 * from the composer.json file
	 */
	public function version(): string|null
	{
		$version = $this->info()['version'] ?? null;

		if (is_string($version) !== true || $version === '') {
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
