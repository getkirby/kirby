<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Jxl;

use Darkroom\Formats\Header;
use Darkroom\Orientation;
use Darkroom\Size;

/**
 * Headers of a JPEG XL codestream, bit by bit
 *
 * @internal
 */
final class Codestream
{
	/**
	 * The most bytes the headers take
	 */
	public const int LENGTH = 24;

	/**
	 * The start of a bare codestream
	 */
	public const string SIGNATURE = "\xFF\x0A";

	/**
	 * Up to 32 bits from `$position` on, lowest first
	 */
	public static function bits(
		string $data,
		int &$position,
		int $count
	): int {
		/** @var array{1: int} $word */
		$word      = unpack('P', $data, $position >> 3);
		$value     = ($word[1] >> ($position & 7)) & ((1 << $count) - 1);
		$position += $count;

		return $value;
	}

	/**
	 * Size, orientation and animation from the headers
	 *
	 * @return array{Header, bool}
	 */
	public static function header(string $data): array
	{
		$end = strlen($data) * 8;

		if (str_starts_with($data, self::SIGNATURE) === false) {
			return [new Header(), false];
		}

		// room for a read of 8 bytes at the end
		$data     = str_pad($data, self::LENGTH + 8, "\0");
		$position = 16;
		$size     = self::size($data, $position);

		if ($position > $end) {
			return [new Header(), false];
		}

		// ImageMetadata: all default, extra fields,
		// orientation, intrinsic size, preview, animation
		if (
			self::bits($data, $position, 1) === 1 ||
			self::bits($data, $position, 1) === 0
		) {
			return [new Header($size), false];
		}

		$orientation = self::bits($data, $position, 3) + 1;

		if (self::bits($data, $position, 1) === 1) {
			self::size($data, $position);
		}

		if (self::bits($data, $position, 1) === 1) {
			self::preview($data, $position);
		}

		$animated = self::bits($data, $position, 1) === 1;

		if ($position > $end) {
			return [new Header($size), false];
		}

		return [new Header($size, Orientation::from($orientation)), $animated];
	}

	/**
	 * Skips a preview header
	 */
	public static function preview(string $data, int &$position): void
	{
		$bits = self::bits($data, $position, 1) === 1 ? [0, 0, 5, 9] : [6, 8, 10, 12];

		$position += $bits[self::bits($data, $position, 2)];

		if (self::bits($data, $position, 3) === 0) {
			$position += $bits[self::bits($data, $position, 2)];
		}
	}

	/**
	 * A height or width
	 */
	public static function side(
		string $data,
		int &$position,
		bool $small
	): int {
		if ($small === true) {
			return (self::bits($data, $position, 5) + 1) * 8;
		}

		$index = self::bits($data, $position, 2);
		return self::bits($data, $position, [9, 13, 18, 30][$index]) + 1;
	}

	/**
	 * A size header
	 */
	public static function size(string $data, int &$position): Size
	{
		$small  = self::bits($data, $position, 1) === 1;
		$height = self::side($data, $position, $small);
		$width  = match (self::bits($data, $position, 3)) {
			0       => self::side($data, $position, $small),
			1       => $height,
			2       => intdiv($height * 12, 10),
			3       => intdiv($height * 4, 3),
			4       => intdiv($height * 3, 2),
			5       => intdiv($height * 16, 9),
			6       => intdiv($height * 5, 4),
			default => $height * 2
		};

		return new Size($width, $height);
	}
}
