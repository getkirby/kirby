<?php

namespace Kirby\Image;

use Stringable;

/**
 * Small class which hold info about the camera
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Camera implements Stringable
{
	protected string|null $make;
	protected string|null $model;

	public function __construct(array $exif)
	{
		$this->make  = $exif['Make'] ?? null;
		$this->model = $exif['Model'] ?? null;
	}

	/**
	 * Returns the make of the camera
	 */
	public function make(): string|null
	{
		return $this->make;
	}

	/**
	 * Returns the camera model
	 */
	public function model(): string|null
	{
		return $this->model;
	}

	/**
	 * Converts the object into a nicely readable array
	 */
	public function toArray(): array
	{
		return [
			'make'  => $this->make,
			'model' => $this->model
		];
	}

	/**
	 * Returns the full make + model name
	 */
	public function __toString(): string
	{
		return trim($this->make . ' ' . $this->model);
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}
}
