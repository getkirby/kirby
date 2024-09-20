<?php

namespace Kirby\Image;

use Stringable;

/**
 * Returns the latitude and longitude values
 * for exif location data if available
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Location implements Stringable
{
	protected float|null $lat = null;
	protected float|null $lng = null;

	/**
	 * Constructor
	 *
	 * @param array $exif The entire exif array
	 */
	public function __construct(array $exif)
	{
		if (
			isset($exif['GPSLatitude']) === true &&
			isset($exif['GPSLatitudeRef']) === true &&
			isset($exif['GPSLongitude']) === true &&
			isset($exif['GPSLongitudeRef']) === true
		) {
			$this->lat = $this->gps(
				$exif['GPSLatitude'],
				$exif['GPSLatitudeRef']
			);
			$this->lng = $this->gps(
				$exif['GPSLongitude'],
				$exif['GPSLongitudeRef']
			);
		}
	}

	/**
	 * Returns the latitude
	 */
	public function lat(): float|null
	{
		return $this->lat;
	}

	/**
	 * Returns the longitude
	 */
	public function lng(): float|null
	{
		return $this->lng;
	}

	/**
	 * Converts the gps coordinates
	 */
	protected function gps(array $coord, string $hemi): float
	{
		$degrees = $coord !== [] ? $this->num($coord[0]) : 0;
		$minutes = count($coord) > 1 ? $this->num($coord[1]) : 0;
		$seconds = count($coord) > 2 ? $this->num($coord[2]) : 0;

		$hemi = strtoupper($hemi);
		$flip = ($hemi === 'W' || $hemi === 'S') ? -1 : 1;

		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
	}

	/**
	 * Converts coordinates to floats
	 */
	protected function num(string $part): float
	{
		$parts = explode('/', $part);

		if (count($parts) === 1) {
			return (float)$parts[0];
		}

		return (float)($parts[0]) / (float)($parts[1]);
	}

	/**
	 * Converts the object into a nicely readable array
	 */
	public function toArray(): array
	{
		return [
			'lat' => $this->lat(),
			'lng' => $this->lng()
		];
	}

	/**
	 * Echos the entire location as lat, lng
	 */
	public function __toString(): string
	{
		return trim($this->lat() . ', ' . $this->lng(), ',');
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
