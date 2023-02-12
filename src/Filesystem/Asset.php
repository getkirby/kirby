<?php

namespace Kirby\Filesystem;

use Kirby\Cms\FileModifications;
use Kirby\Cms\HasMethods;
use Kirby\Exception\BadMethodCallException;

/**
 * Anything in your public path can be converted
 * to an Asset object to use the same handy file
 * methods as for any other Kirby files. Pass a
 * relative path to the class to create the asset.
 *
 * @package   Kirby Filesystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Asset
{
	use IsFile;
	use FileModifications;
	use HasMethods;

	/**
	 * Relative file path
	 */
	protected string|null $path = null;

	/**
	 * Creates a new Asset object for the given path.
	 */
	public function __construct(string $path)
	{
		$this->setProperties([
			'path' => dirname($path),
			'root' => $this->kirby()->root('index') . '/' . $path,
			'url'  => $this->kirby()->url('base') . '/' . $path
		]);
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

		// asset methods
		if ($this->hasMethod($method)) {
			return $this->callMethod($method, $arguments);
		}

		throw new BadMethodCallException('The method: "' . $method . '" does not exist');
	}

	/**
	 * Returns a unique id for the asset
	 */
	public function id(): string
	{
		return $this->root();
	}

	/**
	 * Create a unique media hash
	 */
	public function mediaHash(): string
	{
		return crc32($this->filename()) . '-' . $this->modified();
	}

	/**
	 * Returns the relative path starting at the media folder
	 */
	public function mediaPath(): string
	{
		return 'assets/' . $this->path() . '/' . $this->mediaHash() . '/' . $this->filename();
	}

	/**
	 * Returns the absolute path to the file in the public media folder
	 */
	public function mediaRoot(): string
	{
		return $this->kirby()->root('media') . '/' . $this->mediaPath();
	}

	/**
	 * Returns the absolute Url to the file in the public media folder
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/' . $this->mediaPath();
	}

	/**
	 * Returns the path of the file from the web root,
	 * excluding the filename
	 */
	public function path(): string
	{
		return $this->path;
	}

	/**
	 * Setter for the path
	 *
	 * @return $this
	 */
	protected function setPath(string $path): static
	{
		$this->path = $path === '.' ? '' : $path;
		return $this;
	}
}
