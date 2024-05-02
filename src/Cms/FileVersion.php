<?php

namespace Kirby\Cms;

use Kirby\Filesystem\IsFile;

/**
 * FileVersion
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileVersion
{
	use IsFile;

	protected array $modifications;
	protected $original;

	public function __construct(array $props)
	{
		$this->root          = $props['root'] ?? null;
		$this->url           = $props['url'] ?? null;
		$this->original      = $props['original'];
		$this->modifications = $props['modifications'] ?? [];
	}

	/**
	 * Proxy for public properties, asset methods
	 * and content field getters
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		// asset method proxy
		if (method_exists($this->asset(), $method)) {
			if ($this->exists() === false) {
				$this->save();
			}

			return $this->asset()->$method(...$arguments);
		}

		// content fields
		if ($this->original() instanceof File) {
			return $this->original()->content()->get($method);
		}
	}

	/**
	 * Returns the unique ID
	 */
	public function id(): string
	{
		return dirname($this->original()->id()) . '/' . $this->filename();
	}

	/**
	 * Returns the parent Kirby App instance
	 */
	public function kirby(): App
	{
		return $this->original()->kirby();
	}

	/**
	 * Returns an array with all applied modifications
	 */
	public function modifications(): array
	{
		return $this->modifications;
	}

	/**
	 * Returns the instance of the original File object
	 */
	public function original(): mixed
	{
		return $this->original;
	}

	/**
	 * Applies the stored modifications and
	 * saves the file on disk
	 *
	 * @return $this
	 */
	public function save(): static
	{
		$this->kirby()->thumb(
			$this->original()->root(),
			$this->root(),
			$this->modifications()
		);
		return $this;
	}


	/**
	 * Converts the object to an array
	 */
	public function toArray(): array
	{
		$array = array_merge(
			$this->asset()->toArray(),
			['modifications' => $this->modifications()]
		);

		ksort($array);

		return $array;
	}
}
