<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Png;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\Layout;
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
	public const string SIGNATURE = "\x89PNG\r\n\x1A\n";

	/**
	 * Type and data length of each chunk before the image data
	 *
	 * @return Generator<string, int>
	 */
	protected function chunks(): Generator
	{
		rewind($this->handle);

		if (fread($this->handle, 8) !== self::SIGNATURE) {
			return;
		}

		foreach (Layout::Png->walk($this->handle) as $type => $length) {
			if ($type === 'IDAT') {
				return;
			}

			// libpng ignores EXIF data with JPEG's marker
			if (
				$type === 'eXIf' &&
				Exif::marker($this->handle) === true
			) {
				continue;
			}

			yield $type => $length;
		}
	}

	/**
	 * The color signal of the `cICP` chunk
	 *
	 * @return array{int, int, int, int}
	 */
	public function cicp(): array
	{
		$chunks = $this->chunks();
		$length = Layout::find($chunks, 'cICP');
		$data   = Bytes::bytes($this->handle, min($length, 4));

		if (strlen($data) < 4) {
			return self::UNSPECIFIED;
		}

		/** @var array{int, int, int, int} $codes */
		$codes = array_values((array)unpack('C4', $data));

		return $codes;
	}

	/**
	 * The first `eXIf` chunk
	 */
	public function exif(): string
	{
		$chunks = $this->chunks();
		$length = Layout::find($chunks, 'eXIf');
		return Bytes::bytes($this->handle, $length);
	}

	/**
	 * Size from the `IHDR` chunk, orientation from the first `eXIf` chunk
	 */
	public function header(): Header
	{
		$size        = null;
		$orientation = null;

		foreach ($this->chunks() as $type => $length) {
			if ($type === 'IHDR' && $size === null) {
				$data = Bytes::bytes($this->handle, min($length, 8));
				$size = new Size(0, 0);

				if (strlen($data) === 8) {
					$size = new Size(
						Bytes::int(substr($data, 0, 4)),
						Bytes::int(substr($data, 4))
					);
				}
			}

			if ($type === 'eXIf' && $orientation === null) {
				$orientation = Exif::orientation($this->handle);
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
	 * The profile of the first `iCCP` chunk,
	 * after its name and compression method
	 */
	public function icc(): string
	{
		$chunks = $this->chunks();
		$length = Layout::find($chunks, 'iCCP');
		$data   = Bytes::bytes($this->handle, $length);
		$name   = strpos($data, "\0");

		if ($name === false) {
			return '';
		}

		$data    = substr($data, $name + 2);
		$profile = self::inflate($data);

		return self::profile($profile);
	}

	/**
	 * Inflates zlib data up to about `LIMIT` bytes
	 */
	protected static function inflate(string $data): string
	{
		if (function_exists('gzuncompress') === false) {
			return '';
		}

		return (string)@gzuncompress($data, Bytes::LIMIT);
	}
}
