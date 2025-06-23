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
	protected string $path;


	/**
	 * Creates a new Asset object for the given path.
	 */
	public function __construct(string $path)
	{
		$this->root = $this->kirby()->root('index') . '/' . $path;
		$this->url  = $this->kirby()->url('base') . '/' . $path;

		// set relative file path
		$this->path = dirname($path);

		if ($this->path === '.') {
			$this->path = '';
		}
	}

	/**
	 * Magic caller for asset methods
	 *
	 * @throws \Kirby\Exception\BadMethodCallException
	 */
	public function __call(string $method, array $arguments = []): mixed
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

		throw new BadMethodCallException(
			message: 'The method: "' . $method . '" does not exist'
		);
	}

	/**
	 * Returns a unique id for the asset
	 */
	public function id(): string
	{
		return $this->root();
	}

	/**
	 * Returns the absolute path to the media folder
	 * for the file and its versions
	 * @since 5.0.0
	 */
	public function mediaDir(): string
	{
		return dirname($this->mediaRoot());
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
	public function mediaPath(string|null $filename = null): string
	{
		$filename ??= $this->filename();
		return 'assets/' . $this->path() . '/' . $this->mediaHash() . '/' . $filename;
	}

	/**
	 * Returns the absolute path to the file in the public media folder
	 */
	public function mediaRoot(string|null $filename = null): string
	{
		return $this->kirby()->root('media') . '/' . $this->mediaPath($filename);
	}

	/**
	 * Returns the absolute Url to the file in the public media folder
	 */
	public function mediaUrl(string|null $filename = null): string
	{
		return $this->kirby()->url('media') . '/' . $this->mediaPath($filename);
	}

	/**
	 * Returns the path of the file from the web root,
	 * excluding the filename
	 */
	public function path(): string
	{
		return $this->path;
	}
}
