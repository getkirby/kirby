<?php

namespace Kirby\Image;

/**
 * Camera, location and exposure from an image's EXIF tags
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Exif
{
	protected Camera|null $camera = null;

	/**
	 * The tags of all sections in one list
	 */
	protected array $data;
	protected Location|null $location = null;

	/**
	 * @param array<string, array> $sections Raw tags by section (`IFD0`, `EXIF`, `GPS`, …)
	 * @param $modified The file's time, for images without their own
	 */
	public function __construct(
		array $sections,
		protected int|false $modified = false
	) {
		$this->data = [
			...$sections['IFD0'] ?? [],
			...$sections['EXIF'] ?? [],
			...$sections['GPS'] ?? []
		];
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return [
			...$this->toArray(),
			'camera'   => $this->camera(),
			'location' => $this->location()
		];
	}

	/**
	 * The f-number, e.g. `f/2.8`
	 */
	public function aperture(): string|null
	{
		$fraction = explode('/', (string)($this->data['FNumber'] ?? ''));

		if (count($fraction) !== 2 || (float)$fraction[1] === 0.0) {
			return null;
		}

		return sprintf('f/%.1F', (float)$fraction[0] / (float)$fraction[1]);
	}

	public function camera(): Camera
	{
		return $this->camera ??= new Camera($this->data);
	}

	/**
	 * The raw tags
	 */
	public function data(): array
	{
		return $this->data;
	}

	public function exposure(): string|null
	{
		return $this->data['ExposureTime'] ?? null;
	}

	public function focalLength(): string|null
	{
		return
			$this->data['FocalLength'] ??
			$this->data['FocalLengthIn35mmFilm'] ??
			null;
	}

	public function iso(): string|array|null
	{
		return $this->data['ISOSpeedRatings'] ?? null;
	}

	public function location(): Location
	{
		return $this->location ??= new Location($this->data);
	}

	/**
	 * When the picture was taken, else the file's time
	 */
	public function timestamp(): string|null
	{
		$taken = strtotime($this->data['DateTimeOriginal'] ?? '');

		if ($taken !== false) {
			return (string)$taken;
		}

		if ($this->modified !== false) {
			return (string)$this->modified;
		}

		return null;
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
			'focalLength' => $this->focalLength()
		];
	}
}
