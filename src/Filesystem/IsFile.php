<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\Toolkit\Properties;

/**
 * Trait for all objects that represent an asset file.
 * Adds `::asset()` method which returns either a
 * `Kirby\Filesystem\File` or `Kirby\Image\Image` object.
 * Proxies method calls to this object.
 * @since 3.6.0
 *
 * @package   Kirby Filesystem
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait IsFile
{
	use Properties;

	/**
	 * File asset object
	 */
	protected File|null $asset = null;

	/**
	 * Absolute file path
	 */
	protected string|null $root = null;

	/**
	 * Absolute file URL
	 */
	protected string|null $url = null;

	/**
	 * Constructor sets all file properties
	 */
	public function __construct(array $props)
	{
		$this->setProperties($props);
	}

	/**
	 * Magic caller for asset methods
	 *
	 * @throws \Kirby\Exception\BadMethodCallException
	 */
	public function __call(string $method, array $arguments = [])
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		// asset method proxy
		if (method_exists($this->asset(), $method)) {
			return $this->asset()->$method(...$arguments);
		}

		throw new BadMethodCallException('The method: "' . $method . '" does not exist');
	}

	/**
	 * Converts the asset to a string
	 */
	public function __toString(): string
	{
		return (string)$this->asset();
	}

	/**
	 * Returns the file asset object
	 */
	public function asset(array|string|null $props = null): File
	{
		if ($this->asset !== null) {
			return $this->asset;
		}

		$props ??= [];

		if (is_string($props) === true) {
			$props = ['root' => $props];
		}

		$props['model'] ??= $this;

		return $this->asset = match ($this->type()) {
			'image' => new Image($props),
			default => new File($props)
		};
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
	 * To check the existence of the IsFile trait
	 *
	 * @todo Switch to class constant in traits when min PHP version 8.2 required
	 * @codeCoverageIgnore
	 */
	protected function hasIsFileTrait(): bool
	{
		return true;
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
	 * Setter for the root
	 *
	 * @return $this
	 */
	protected function setRoot(string|null $root = null): static
	{
		$this->root = $root;
		return $this;
	}

	/**
	 * Setter for the file url
	 *
	 * @return $this
	 */
	protected function setUrl(string|null $url = null): static
	{
		$this->url = $url;
		return $this;
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
