<?php

declare(strict_types = 1);

namespace Darkroom;

/**
 * Categories of the EXIF tags a thumb or a stripped image keeps
 */
enum Meta: string
{
	case Camera      = 'camera';
	case Description = 'description';
	case Location    = 'location';
	case Rights      = 'rights';
	case Time        = 'time';

	/**
	 * The short name in a plan's key
	 */
	public function code(): string
	{
		return match ($this) {
			self::Camera      => 'cam',
			self::Description => 'desc',
			self::Location    => 'loc',
			self::Rights      => 'rights',
			self::Time        => 'time',
		};
	}

	/**
	 * The EXIF tags of the categories
	 *
	 * @param list<Meta> $meta
	 * @return list<int>
	 */
	public static function exif(array $meta): array
	{
		$lists = array_map(fn (Meta $category) => $category->tags(), $meta);

		return array_merge(...$lists);
	}

	/**
	 * The categories that a `strip` option keeps
	 *
	 * @internal
	 * @param bool|list<Meta> $strip
	 * @return list<Meta>
	 */
	public static function kept(bool|array $strip): array
	{
		if ($strip === true) {
			return [];
		}

		if ($strip === false) {
			return self::cases();
		}

		$cases = array_filter(
			self::cases(),
			fn (Meta $case) => in_array($case, $strip, true) === false
		);

		return array_values($cases);
	}

	/**
	 * The EXIF tags, which are unique across IFDs
	 *
	 * @return list<int>
	 */
	public function tags(): array
	{
		return match ($this) {
			// make and model; exposure, aperture, ISO, exposure program and bias,
			// largest aperture, metering, flash, focal length, exposure mode,
			// white balance, 35 mm focal length, lens range, make and model
			self::Camera => [
				0x010F, 0x0110, 0x829A, 0x829D, 0x8827, 0x8822, 0x9204, 0x9205, 0x9207,
				0x9209, 0x920A, 0xA402, 0xA403, 0xA405, 0xA432, 0xA433, 0xA434
			],
			// the caption and title
			self::Description => [0x010E, 0xA436],
			// all of GPS
			self::Location => range(0x00, 0x1F),
			// artist, copyright and photographer
			self::Rights => [0x013B, 0x8298, 0xA437],
			// changed, taken and digitized, each with its offset
			// from UTC and fractions of seconds
			self::Time => [0x0132, 0x9003, 0x9004, 0x9010, 0x9011, 0x9012, 0x9290, 0x9291, 0x9292],
		};
	}
}
