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
class Crop
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
		[$xf, $yf]   = static::focus($this->focus);

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
	 * Transforms the focal point's string value (from content field)
	 * to a [x, y] array (values 0.0-1.0)
	 */
	public static function focus(string $value): array
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
}
