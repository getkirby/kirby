<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Jxl;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\Layout;
use Darkroom\Formats\Header;
use Darkroom\Formats\Reader as Base;
use Darkroom\Meta\Exif;
use Generator;

/**
 * JPEG XL files, bare or in an ISO BMFF container
 *
 * @internal
 */
final class Reader extends Base
{
	public const string SIGNATURE = "\0\0\0\x0CJXL \r\n\x87\n";

	/**
	 * Type and data length of each box of a container
	 *
	 * @return Generator<string, int>
	 */
	protected function boxes(): Generator
	{
		rewind($this->handle);

		if (fread($this->handle, 12) === self::SIGNATURE) {
			yield from Layout::Box->walk($this->handle);
		}
	}

	/**
	 * The codestream's headers
	 */
	protected function codestream(): string
	{
		rewind($this->handle);

		$data = (string)fread($this->handle, 12);

		// a bare codestream, or none
		if ($data !== self::SIGNATURE) {
			return $data . (string)fread($this->handle, Codestream::LENGTH - 12);
		}

		$data = '';

		foreach (Layout::Box->walk($this->handle) as $type => $length) {
			// partial codestreams start with their index
			if ($type === 'jxlp') {
				$part  = Bytes::bytes($this->handle, min($length, Codestream::LENGTH + 4 - strlen($data)));
				$data .= substr($part, 4);
			}

			if ($type === 'jxlc') {
				$data .= Bytes::bytes($this->handle, min($length, Codestream::LENGTH - strlen($data)));
			}

			if (strlen($data) >= Codestream::LENGTH) {
				break;
			}
		}

		return $data;
	}

	/**
	 * The `Exif` box
	 */
	public function exif(): string
	{
		$boxes  = $this->boxes();
		$length = Layout::find($boxes, 'Exif');
		return Exif::tiff($this->handle, $length, offset: true);
	}

	/**
	 * The orientation is in the codestream, not in EXIF data
	 */
	public function header(): Header
	{
		$data = $this->codestream();
		return Codestream::header($data)[0];
	}

	public function isAnimated(): bool
	{
		$data = $this->codestream();
		return Codestream::header($data)[1];
	}
}
