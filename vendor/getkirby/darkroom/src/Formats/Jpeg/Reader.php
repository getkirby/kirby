<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Jpeg;

use Darkroom\Containers\Bytes;
use Darkroom\Formats\Header;
use Darkroom\Formats\Reader as Base;
use Darkroom\Meta\Exif;
use Darkroom\Orientation;
use Darkroom\Size;
use Generator;

/**
 * @internal
 */
final class Reader extends Base
{
	/**
	 * The start of an APP2 segment of the color profile
	 */
	public const string ICC = "ICC_PROFILE\0";

	/**
	 * The start of image
	 */
	public const string SIGNATURE = "\xFF\xD8";

	/**
	 * The first `Exif` APP1 segment
	 */
	public function exif(): string
	{
		foreach ($this->segments() as $marker => $length) {
			if (
				$marker === 0xE1 &&
				Exif::marker($this->handle) === true
			) {
				return Bytes::bytes($this->handle, $length - 6);
			}
		}

		return '';
	}

	/**
	 * Size from the first frame header,
	 * orientation from the first `Exif` APP1 segment
	 */
	public function header(): Header
	{
		$size        = null;
		$orientation = null;

		foreach ($this->segments() as $marker => $length) {
			if (
				$marker === 0xE1 &&
				$orientation === null &&
				Exif::marker($this->handle) === true
			) {
				$orientation = Exif::orientation($this->handle);
			}

			// SOF0–SOF15 except DHT, JPG and DAC: precision, height, width
			if (
				$size === null &&
				$marker >= 0xC0 &&
				$marker <= 0xCF &&
				in_array($marker, [0xC4, 0xC8, 0xCC], true) === false
			) {
				$frame = Bytes::bytes($this->handle, min($length, 5));
				$size  = new Size(0, 0);

				if (strlen($frame) === 5) {
					$width  = Bytes::int(substr($frame, 3, 2));
					$height = Bytes::int(substr($frame, 1, 2));
					$size   = new Size($width, $height);
				}
			}

			if ($size !== null && $orientation !== null) {
				break;
			}
		}

		return new Header(
			$size ?? new Size(0, 0),
			$orientation ?? Orientation::None
		);
	}

	/**
	 * The profile joined from the `ICC_PROFILE` APP2 segments
	 */
	public function icc(): string
	{
		$chunks = [];
		$total  = 0;

		foreach ($this->segments() as $marker => $length) {
			if ($marker !== 0xE2 || $length < 14) {
				continue;
			}

			$head = Bytes::bytes($this->handle, 14);

			if (str_starts_with($head, self::ICC) === false) {
				continue;
			}

			// the sequence number, from 1
			$number = ord($head[12] ?? "\0");
			$total += $length - 14;

			if ($total > Bytes::LIMIT) {
				return '';
			}

			$chunks[$number] = Bytes::bytes($this->handle, $length - 14);
		}

		ksort($chunks);

		$profile = implode('', $chunks);

		return self::profile($profile);
	}

	/**
	 * Marker and data length of each segment before
	 * the scan, past stray bytes, as decoders read on
	 *
	 * @return Generator<int, int>
	 */
	protected function segments(): Generator
	{
		rewind($this->handle);

		if (fread($this->handle, 2) !== self::SIGNATURE) {
			return;
		}

		$next  = (int)ftell($this->handle);
		$skips = 0;

		for ($step = 0; $step < Bytes::STEPS; $step++) {
			// the marker, then a length that counts its own two bytes
			$head  = (string)fread($this->handle, 4);
			$next += 4;

			// past fill and stray bytes
			while (
				strlen($head) === 4 &&
				($head[0] !== "\xFF" || $head[1] === "\xFF" || $head[1] === "\0") &&
				$skips++ < Bytes::LIMIT
			) {
				$head = substr($head, 1) . (string)fread($this->handle, 1);
				$next++;
			}

			$marker = ord($head[1] ?? "\0");
			$length = Bytes::int(substr($head, 2)) - 2;

			// none, start of scan, end of image
			if (
				strlen($head) < 4 ||
				in_array($marker, [0x00, 0xFF, 0xDA, 0xD9], true) === true ||
				$length < 0
			) {
				return;
			}

			$next += $length;

			yield $marker => $length;

			if (ftell($this->handle) !== $next) {
				fseek($this->handle, $next);
			}
		}
	}
}
