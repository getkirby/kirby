<?php

declare(strict_types = 1);

namespace Darkroom\Formats;

use Darkroom\Format;

/**
 * Copies an image file without its meta
 *
 * @internal
 */
abstract class Writer
{
	/**
	 * Whether the format keeps the orientation in its EXIF data
	 *
	 * @var bool
	 */
	public const bool ORIENTS = false;

	/**
	 * The encoded image with the EXIF data and profile
	 */
	public static function embed(
		string $data,
		Format $format,
		string $exif = '',
		string|null $icc = null
	): string {
		$writer = self::for($format);
		$in     = fopen('php://memory', 'w+b');
		$out    = fopen('php://memory', 'w+b');

		if ($in === false || $out === false) {
			return $data;
		}

		fwrite($in, $data);
		rewind($in);

		$writer::write($in, $out, $exif, $icc);

		rewind($out);

		return (string)stream_get_contents($out);
	}

	/**
	 * @return class-string<Writer>
	 */
	public static function for(Format $format): string
	{
		return match ($format) {
			Format::Avif,
			Format::Heic => Heif\Writer::class,
			Format::Gif  => Gif\Writer::class,
			Format::Jpeg => Jpeg\Writer::class,
			Format::Jxl  => Jxl\Writer::class,
			Format::Png  => Png\Writer::class,
			Format::Webp => Webp\Writer::class,
		};
	}

	/**
	 * Copies the image without meta, but with the EXIF data,
	 * and with the profile or else the image's own
	 *
	 * @param resource $handle
	 * @param resource $out
	 * @param $exif TIFF data
	 * @param $icc A profile instead of the image's own, `''` for none
	 */
	abstract public static function write(
		$handle,
		$out,
		string $exif = '',
		string|null $icc = null
	): void;
}
