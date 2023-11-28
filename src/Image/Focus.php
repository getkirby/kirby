<?php

namespace Kirby\Image;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Focus
{
	/**
	 * Generates crop coordinates based on focal point
	 */
	public static function coords(
		string $crop,
		int $sourceWidth,
		int $sourceHeight,
		int $width,
		int $height
	): array|null {
		[$x, $y] = static::parse($crop);

		// determine aspect ratios
		$ratioSource = static::ratio($sourceWidth, $sourceHeight);
		$ratioThumb  = static::ratio($width, $height);

		// no cropping necessary
		if ($ratioSource == $ratioThumb) {
			return null;
		}

		// defaults
		$width  = $sourceWidth;
		$height = $sourceHeight;

		if ($ratioThumb > $ratioSource) {
			$height = $sourceWidth / $ratioThumb;
		} else {
			$width = $sourceHeight * $ratioThumb;
		}

		// calculate focus for original image
		$x = $sourceWidth * $x;
		$y = $sourceHeight * $y;

		$x1 = max(0, $x - $width / 2);
		$y1 = max(0, $y - $height / 2);

		// off canvas?
		if ($x1 + $width > $sourceWidth) {
			$x1 = $sourceWidth - $width;
		}

		if ($y1 + $height > $sourceHeight) {
			$y1 =  $sourceHeight - $height;
		}

		return [
			'x1'     => (int)floor($x1),
			'y1'     => (int)floor($y1),
			'x2'     => (int)floor($x1 + $width),
			'y2'     => (int)floor($y1 + $height),
			'width'  => (int)floor($width),
			'height' => (int)floor($height),
		];
	}

	public static function isFocalPoint(string $value): bool
	{
		return Str::contains($value, '%') === true;
	}

	/**
	 * Transforms the focal point's string value (from content field)
	 * to a [x, y] array (values 0.0-1.0)
	 */
	public static function parse(string $value): array
	{
		// support for former Focus plugin
		if (Str::startsWith($value, '{') === true) {
			$focus = json_decode($value);
			return [$focus->x, $focus->y];
		}

		preg_match_all("/(\d{1,3}\.?\d*)[%|,|\s]*/", $value, $points);

		return A::map(
			$points[1],
			function ($point) {
				$point = (float)$point;
				$point = $point > 1 ? $point / 100 : $point;
				return round($point, 3);
			}
		);
	}

	/**
	 * Calculates the image ratio
	 */
	public static function ratio(int $width, int $height): float
	{
		return $height !== 0 ? $width / $height : 0;
	}
}
