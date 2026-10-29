<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;

/**
 * Trait for all objects that represent an asset file.
 * Adds `::asset()` method which returns a
 * `Kirby\Filesystem\File` object.
 * Proxies method calls to this object.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.6.0
 */
trait IsFile
{
	/**
	 * Marker constant for detecting use of this trait
	 */
	public const IS_FILE_TRAIT = true;

	/**
	 * File asset object
	 */
	protected File|null $asset = null;

	/**
	 * Absolute file path
	 */
	protected string|null $root;

	/**
	 * Absolute file URL
	 */
	protected string|null $url;

	/**
	 * Constructor sets all file properties
	 */
	public function __construct(array $props)
	{
		$this->root = $props['root'] ?? null;
		$this->url  = $props['url'] ?? null;
	}

	/**
	 * Magic caller for asset methods
	 *
	 * @throws BadMethodCallException
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		// asset method proxy
		if (method_exists($this->asset(), $method) === true) {
			return $this->asset()->$method(...$arguments);
		}

		throw new BadMethodCallException(
			message: 'The method: "' . $method . '" does not exist'
		);
	}

	/**
	 * Converts the asset to a string
	 */
	public function __toString(): string
	{
		return (string)$this->asset();
	}

	/**
	 * Returns the file asset object. A new object will be created if it doesn't
	 * exist yet. The instance will be cached to avoid multiple instantiations,
	 * when calling asset methods.
	 */
	public function asset(array|string|null $props = null): File
	{
		return $this->asset ??= $this->assetFactory($props ?? []);
	}

	/**
	 * Creates a new asset object
	 */
	protected function assetFactory(array|string $props = []): File
	{
		if (is_string($props) === true) {
			$props = ['root' => $props];
		}

		$props['model'] ??= $this;

		return new File($props);
	}

	/**
	 * Checks if the file exists on disk
	 */
	public function exists(): bool
	{
		// Important to include this in the trait
		// to avoid infinite loops when trying
		// to proxy the method from the asset object
		return file_exists($this->root()) === true;
	}

	/**
	 * Returns the app instance
	 */
	public function kirby(): App
	{
		return App::instance();
	}

	/**
	 * Returns the given file path
	 */
	public function root(): string|null
	{
		return $this->root;
	}

	/**
	 * Returns the file type
	 */
	public function type(): string|null
	{
		// Important to include this in the trait
		// to avoid infinite loops when trying
		// to proxy the method from the asset object
		return F::type($this->root() ?? $this->url());
	}

	/**
	 * Returns the absolute url for the file
	 */
	public function url(): string|null
	{
		return $this->url;
	}
}
