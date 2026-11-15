<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Webp;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\Layout;
use Darkroom\Formats\Header;
use Darkroom\Formats\Reader as Base;
use Darkroom\Meta\Exif;

/**
 * @internal
 */
final class Reader extends Base
{
	/**
	 * Header and first 10 data bytes of the first chunk
	 */
	protected function chunk(): string
	{
		rewind($this->handle);

		$head = (string)fread($this->handle, 30);

		if (
			str_starts_with($head, 'RIFF') === false ||
			substr($head, 8, 4) !== 'WEBP'
		) {
			return '';
		}

		return substr($head, 12);
	}

	/**
	 * The `EXIF` chunk
	 */
	public function exif(): string
	{
		$chunk  = $this->chunk();
		$length = $this->locate($chunk, 'EXIF');
		return Exif::tiff($this->handle, $length);
	}

	/**
	 * Size from the first chunk, orientation from the `EXIF` chunk
	 */
	public function header(): Header
	{
		$chunk = $this->chunk();
		$size  = Vp8x::size($chunk);

		if ($this->locate($chunk, 'EXIF') === 0) {
			return new Header($size);
		}

		$orientation = Exif::orientation($this->handle);
		return new Header($size, $orientation);
	}

	/**
	 * The `ICCP` chunk
	 */
	public function icc(): string
	{
		$chunk   = $this->chunk();
		$length  = $this->locate($chunk, 'ICCP');
		$profile = Bytes::bytes($this->handle, $length);
		return self::profile($profile);
	}

	/**
	 * An extended file with the animation flag
	 */
	public function isAnimated(): bool
	{
		$chunk = $this->chunk();
		$flags = Vp8x::flags($chunk);
		return ($flags & Vp8x::FLAGS['ANIM']) !== 0;
	}

	/**
	 * Data length of the chunk, with the handle at
	 * its data; libwebp needs the flag for it
	 *
	 * @param key-of<Vp8x::FLAGS> $type
	 */
	protected function locate(string $chunk, string $type): int
	{
		if ((Vp8x::flags($chunk) & Vp8x::FLAGS[$type]) === 0) {
			return 0;
		}

		// on after `VP8X`, where `chunk()` leaves the handle
		$chunks = Layout::Riff->walk($this->handle);
		return Layout::find($chunks, $type);
	}
}
