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
	public static array $positions = [
		'center'       => '50% 50%',
		'top'          => '50% 0%',
		'right'        => '100% 50%',
		'bottom'       => '50% 100%',
		'left'         => '0% 50%',
		'top left'     => '0% 0%',
		'top right'    => '100% 0%',
		'bottom right' => '100% 100%',
		'bottom left'  => '0% 100%',
	];

	public int $x1;
	public int $y1;
	public int $x2;
	public int $y2;
	public int $targetHeight;
	public int $scaledWidth;
	public int $scaledHeight;
	public string $focus;

	public function __construct(
		public int $sourceWidth,
		public int $sourceHeight,
		public int $targetWidth,
		int|null $targetHeight = null,
		string $focus = 'center'
	) {
		$this->targetHeight ??= $targetWidth;
		$this->focus = static::$positions[$focus] ?? $focus;
		[$xf, $yf]   = static::parse($this->focus);

		// determine scaling ratios
		$ratioWidth   = $this->sourceWidth / $targetWidth;
		$ratioHeight  = $this->sourceHeight / $targetHeight;
		$scaleRatio   = min($ratioWidth, $ratioHeight);

		$this->scaledWidth  = (int)floor($targetWidth * $scaleRatio);
		$this->scaledHeight = (int)floor($targetHeight * $scaleRatio);

		// calculate focus coordinates
		$x = $xf * $this->sourceWidth;
		$y = $yf * $this->sourceHeight;

		$x1min = 0;
		$x1max = $this->sourceWidth - $this->scaledWidth;
		$x1    = max($x1min, min($x - $this->scaledWidth/2, $x1max));

		$y1min = 0;
		$y1max = $this->sourceHeight - $this->scaledHeight;
		$y1    = max($y1min, min($y - $this->scaledHeight/2, $y1max));

		$this->x1 = (int)floor($x1);
		$this->y1 = (int)floor($y1);
		$this->x2 = (int)floor($x1 + $this->scaledWidth);
		$this->y2 = (int)floor($y1 + $this->scaledHeight);
	}


	/**
	 * Generates crop coordinates based on focal point
	 * @deprecated will be removed in v5.0
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

	public static function fromDarkroomOptions(array $options): static
	{
		return new static(
			sourceWidth: $options['sourceWidth'],
			sourceHeight: $options['sourceHeight'],
			targetWidth: $options['width'],
			targetHeight: $options['height'],
			focus: $options['crop']
		);
	}

	/**
	 * @deprecated will be removed in v5.0
	 */
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
	 * @todo rename parameters to $a, $b in v5.0
	 */
	public static function ratio(int $width, int $height): float
	{
		return $height !== 0 ? $width / $height : 0;
	}
}
