<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Heif;

use Darkroom\Containers\Bytes;
use Darkroom\Formats\Header;
use Darkroom\Formats\Reader as Base;
use Darkroom\Meta\Exif;
use Darkroom\Orientation;
use Darkroom\Rect;
use Darkroom\Size;

/**
 * HEIC and AVIF files
 *
 * @internal
 */
final class Reader extends Base
{
	/**
	 * By a horizontal flip or none,
	 * then by clockwise quarter turns
	 */
	protected const array ORIENTATIONS = [
		[
			Orientation::None,
			Orientation::Rotate90,
			Orientation::Rotate180,
			Orientation::Rotate270
		],
		[
			Orientation::FlipHorizontal,
			Orientation::Transverse,
			Orientation::FlipVertical,
			Orientation::Transpose
		]
	];

	/**
	 * The clean aperture, which some decoders ignore
	 */
	public function canvas(): Rect|null
	{
		$properties         = Properties::of($this->handle, ['clap', 'ispe']);
		[$image, $aperture] = Aperture::of($properties);

		if ($aperture == new Rect(0, 0, $image->width, $image->height)) {
			return null;
		}

		return $aperture;
	}

	/**
	 * The color signal in the primary item's `nclx` color property
	 *
	 * @return array{int, int, int, int}
	 */
	public function cicp(): array
	{
		$properties = Properties::of($this->handle, ['colr']);

		// the type, three 16-bit codes, the range in the top bit
		foreach ($properties as [, $data]) {
			if (
				str_starts_with($data, 'nclx') === true &&
				strlen($data) >= 11
			) {
				/** @var array{code1: int, code2: int, code3: int, range: int} $codes */
				$codes = unpack('n3code/Crange', $data, 4);

				return [
					$codes['code1'],
					$codes['code2'],
					$codes['code3'],
					$codes['range'] >> 7
				];
			}
		}

		return self::UNSPECIFIED;
	}

	/**
	 * The `Exif` item that describes the primary item
	 */
	public function exif(): string
	{
		$meta = Meta::index($this->handle);
		$item = Items::describing($meta, 'Exif');

		if ($item === null) {
			return '';
		}

		$iloc    = Iloc::from($meta);
		$extents = $iloc->extents([$item => 'Exif']);

		// its first extent
		[$offset, $length] = $extents[$item][0] ?? [0, 0];

		Bytes::seek($this->handle, $offset);

		return Exif::tiff($this->handle, $length, offset: true);
	}

	/**
	 * Size and orientation from the primary item's properties,
	 * which EXIF doesn't override
	 */
	public function header(): Header
	{
		$properties   = Properties::of($this->handle, ['clap', 'imir', 'irot', 'ispe']);
		[, $aperture] = Aperture::of($properties);
		$size         = new Size($aperture->width, $aperture->height);
		$flip         = 0;
		$turns        = 0;

		foreach ($properties as [$type, $data]) {
			// anticlockwise quarter turns
			if ($type === 'irot') {
				$turns -= Bytes::int($data) & 3;
			}

			// a mirror after the turns is a flip before them, the turns reversed;
			// mirroring top and bottom (0) is a flip and half a turn
			if ($type === 'imir') {
				$flip  = 1 - $flip;
				$turns = (Bytes::int($data) & 1) === 1 ? -$turns : 2 - $turns;
			}
		}

		$orientation = self::ORIENTATIONS[$flip][$turns & 3];

		return new Header($size, $orientation);
	}

	/**
	 * The ICC profile of the primary item's `colr` property
	 */
	public function icc(): string
	{
		$properties = Properties::of($this->handle, ['colr'], Bytes::LIMIT);

		foreach ($properties as [, $data]) {
			$type = substr($data, 0, 4);

			if ($type === 'prof' || $type === 'rICC') {
				$profile = substr($data, 4);

				return self::profile($profile);
			}
		}

		return '';
	}
}
