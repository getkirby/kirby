<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Jpeg;

use Darkroom\Containers\Bytes;
use Darkroom\Formats\Writer as Base;

/**
 * @internal
 */
final class Writer extends Base
{
	/**
	 * The most bytes of data in a segment, after its length
	 */
	protected const int DATA = 65533;
	public const bool ORIENTS = true;

	/**
	 * `ICC_PROFILE` APP2 segments of the profile
	 */
	protected static function profile(string $profile): string
	{
		if ($profile === '') {
			return '';
		}

		// room after the marker, the sequence number and count
		$parts    = str_split($profile, self::DATA - 14);
		$count    = chr(count($parts));
		$segments = '';

		foreach ($parts as $index => $part) {
			$segment   = self::segment(0xE2, Reader::ICC . chr($index + 1) . $count . $part);
			$segments .= $segment;
		}

		return $segments;
	}

	/**
	 * Copies the bytes up to the next marker, or skips them without `$out`
	 *
	 * @param resource $handle
	 * @param resource|null $out
	 */
	protected static function scan($handle, $out): void
	{
		while (($block = (string)fread($handle, 8192)) !== '') {
			// the byte after a last 0xFF may be in the next block
			while (str_ends_with($block, "\xFF") === true && ($byte = (string)fread($handle, 1)) !== '') {
				$block .= $byte;
			}

			// a marker: not a stuffed zero, a restart marker or a fill byte
			$found = preg_match('/\xFF[^\x00\xD0-\xD7\xFF]/', $block, $match, PREG_OFFSET_CAPTURE);
			$end   = $found === 1 ? $match[0][1] : strlen($block);

			if ($out !== null) {
				fwrite($out, substr($block, 0, $end));
			}

			// back to the marker
			if ($found === 1) {
				fseek($handle, $end - strlen($block), SEEK_CUR);
				return;
			}
		}
	}

	/**
	 * The segment with its marker and length, which counts itself
	 */
	protected static function segment(int $marker, string $data): string
	{
		if (strlen($data) > self::DATA) {
			return '';
		}

		return "\xFF" . chr($marker) . pack('n', strlen($data) + 2) . $data;
	}

	/**
	 * Keeps JFIF without its thumbnail, the color profile
	 * and Adobe's color transform; the meta after JFIF
	 *
	 * @param resource $handle
	 * @param resource $out
	 */
	public static function write(
		$handle,
		$out,
		string $exif = '',
		string|null $icc = null
	): void {
		$own   = $icc === null;
		$app1  = $exif === '' ? '' : self::segment(0xE1, "Exif\0\0" . $exif);
		$after = $app1 . self::profile($icc ?? '');
		$scan  = false;

		Bytes::seek($handle, 2);
		fwrite($out, Reader::SIGNATURE);

		while (true) {
			// the entropy-coded data after a start of scan, else stray bytes
			self::scan($handle, $scan === true ? $out : null);

			// the end of the file as end of image;
			// the length counts its own two bytes
			$head   = (string)fread($handle, 4);
			$marker = ord($head[1] ?? "\xD9");
			$length = Bytes::int(substr($head, 2)) - 2;

			if ($marker === 0xD9) {
				fwrite($out, "\xFF\xD9");
				return;
			}

			if (strlen($head) < 4 || $length < 0) {
				return;
			}

			$data = Bytes::bytes($handle, $length);
			$scan = $marker === 0xDA;

			// no other application segments
			$keep = match ($marker) {
				0xE0    => str_starts_with($data, "JFIF\0"),
				0xE2    => $own === true && str_starts_with($data, Reader::ICC),
				0xEE    => str_starts_with($data, 'Adobe'),
				0xFE    => false,
				default => $marker < 0xE0 || $marker > 0xEF
			};

			if ($keep === false) {
				continue;
			}

			// JFIF with a 0 × 0 thumbnail, the meta after it
			if ($marker === 0xE0) {
				$data = substr($data, 0, 12) . "\0\0";
			} else {
				fwrite($out, $after);
				$after = '';
			}

			$segment = self::segment($marker, $data);
			fwrite($out, $segment);
		}
	}
}
