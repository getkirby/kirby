<?php

declare(strict_types = 1);

namespace Darkroom\Formats;

use Darkroom\Containers\Bytes;
use Darkroom\Format;
use Darkroom\Rect;

/**
 * Reads a container format from a file's handle, which it closes
 *
 * @internal
 */
abstract class Reader
{
	/**
	 * The color signal libheif gives a file without one
	 */
	public const array UNSPECIFIED = [2, 2, 2, 1];

	/**
	 * @param resource $handle
	 */
	final public function __construct(
		protected $handle,
		public readonly Format|null $format = null
	) {
	}

	public function __destruct()
	{
		fclose($this->handle);
	}

	public function canvas(): Rect|null
	{
		return null;
	}

	/**
	 * The color signal: primaries, transfer,
	 * matrix and full range (H.273)
	 *
	 * @return array{int, int, int, int}
	 */
	public function cicp(): array
	{
		return self::UNSPECIFIED;
	}

	public function exif(): string
	{
		return '';
	}

	/**
	 * @return class-string<Reader>
	 */
	public static function for(Format $format): string
	{
		return match ($format) {
			Format::Avif,
			Format::Heic => Heif\Reader::class,
			Format::Gif  => Gif\Reader::class,
			Format::Jpeg => Jpeg\Reader::class,
			Format::Jxl  => Jxl\Reader::class,
			Format::Png  => Png\Reader::class,
			Format::Webp => Webp\Reader::class,
		};
	}

	abstract public function header(): Header;

	public function icc(): string
	{
		return '';
	}

	public function isAnimated(): bool
	{
		return false;
	}

	/**
	 * The reader for the file's format
	 */
	public static function open(string $file): self|null
	{
		// fopen() throws for an empty path
		$handle = $file === '' ? false : @fopen($file, 'rb');

		if ($handle === false) {
			return null;
		}

		// a directory opens, but can't be read
		$format = Format::detect((string)@fread($handle, 64));

		if ($format === null) {
			fclose($handle);
			return null;
		}

		$reader = self::for($format);

		return new $reader($handle, $format);
	}

	/**
	 * The ICC profile at the start of the data, cut to its length
	 */
	public static function profile(string $data): string
	{
		$length = Bytes::int(substr($data, 0, 4));

		// the header and tag count, and its signature
		if (
			$length < 132 ||
			$length > strlen($data) ||
			substr($data, 36, 4) !== 'acsp'
		) {
			return '';
		}

		return substr($data, 0, $length);
	}
}
