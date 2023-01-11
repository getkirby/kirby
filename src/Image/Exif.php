<?php

namespace Kirby\Image;

use Kirby\Toolkit\V;

/**
 * Reads exif data from a given image object
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Exif
{
	/**
	 * The parent image object
	 */
	protected Image $image;

	/**
	 * The raw exif array
	 */
	protected array $data = [];

	/**
	 * The camera object with model and make
	 */
	protected Camera|null $camera = null;

	/**
	 * The location object
	 */
	protected Location|null $location = null;

	/**
	 * The timestamp
	 */
	protected string|null $timestamp = null;

	/**
	 * The exposure value
	 */
	protected string|null $exposure = null;

	/**
	 * The aperture value
	 */
	protected string|null $aperture = null;

	/**
	 * ISO value
	 */
	protected string|null $iso = null;

	/**
	 * Focal length
	 */
	protected string|null $focalLength = null;

	/**
	 * Color or black/white
	 */
	protected bool|null $isColor = null;

	public function __construct(Image $image)
	{
		$this->image = $image;
		$this->data  = $this->read();
		$this->parse();
	}

	/**
	 * Returns the raw data array from the parser
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Returns the Camera object
	 */
	public function camera(): Camera
	{
		if ($this->camera !== null) {
			return $this->camera;
		}

		return $this->camera = new Camera($this->data);
	}

	/**
	 * Returns the location object
	 */
	public function location(): Location
	{
		if ($this->location !== null) {
			return $this->location;
		}

		return $this->location = new Location($this->data);
	}

	/**
	 * Returns the timestamp
	 */
	public function timestamp(): string|null
	{
		return $this->timestamp;
	}

	/**
	 * Returns the exposure
	 */
	public function exposure(): string|null
	{
		return $this->exposure;
	}

	/**
	 * Returns the aperture
	 */
	public function aperture(): string|null
	{
		return $this->aperture;
	}

	/**
	 * Returns the iso value
	 */
	public function iso(): string|null
	{
		return $this->iso;
	}

	/**
	 * Checks if this is a color picture
	 */
	public function isColor(): bool|null
	{
		return $this->isColor;
	}

	/**
	 * Checks if this is a bw picture
	 */
	public function isBW(): bool|null
	{
		return ($this->isColor !== null) ? $this->isColor === false : null;
	}

	/**
	 * Returns the focal length
	 */
	public function focalLength(): string|null
	{
		return $this->focalLength;
	}

	/**
	 * Read the exif data of the image object if possible
	 */
	protected function read(): array
	{
		// @codeCoverageIgnoreStart
		if (function_exists('exif_read_data') === false) {
			return [];
		}
		// @codeCoverageIgnoreEnd

		$data = @exif_read_data($this->image->root());
		return is_array($data) ? $data : [];
	}

	/**
	 * Get all computed data
	 */
	protected function computed(): array
	{
		return $this->data['COMPUTED'] ?? [];
	}

	/**
	 * Parses and stores all relevant exif data
	 */
	protected function parse(): void
	{
		$this->timestamp   = $this->parseTimestamp();
		$this->exposure    = $this->data['ExposureTime'] ?? null;
		$this->iso         = $this->data['ISOSpeedRatings'] ?? null;
		$this->focalLength = $this->parseFocalLength();
		$this->aperture    = $this->computed()['ApertureFNumber'] ?? null;
		$this->isColor     = V::accepted($this->computed()['IsColor'] ?? null);
	}

	/**
	 * Return the timestamp when the picture has been taken
	 */
	protected function parseTimestamp(): string
	{
		if (isset($this->data['DateTimeOriginal']) === true) {
			if ($time = strtotime($this->data['DateTimeOriginal'])) {
				return (string)$time;
			}
		}

		return $this->data['FileDateTime'] ?? $this->image->modified();
	}

	/**
	 * Return the focal length
	 */
	protected function parseFocalLength(): string|null
	{
		return $this->data['FocalLength'] ??
			   $this->data['FocalLengthIn35mmFilm'] ??
			   null;
	}

	/**
	 * Converts the object into a nicely readable array
	 */
	public function toArray(): array
	{
		return [
			'camera'      => $this->camera()->toArray(),
			'location'    => $this->location()->toArray(),
			'timestamp'   => $this->timestamp(),
			'exposure'    => $this->exposure(),
			'aperture'    => $this->aperture(),
			'iso'         => $this->iso(),
			'focalLength' => $this->focalLength(),
			'isColor'     => $this->isColor()
		];
	}

	/**
	 * Improved `var_dump` output
	 */
	public function __debugInfo(): array
	{
		return array_merge($this->toArray(), [
			'camera'   => $this->camera(),
			'location' => $this->location()
		]);
	}
}
