<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Webp;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\Layout;
use Darkroom\Containers\Splice;
use Darkroom\Formats\Writer as Base;

/**
 * @internal
 */
final class Writer extends Base
{
	/**
	 * The chunks of the image
	 */
	protected const array KEEP = ['ALPH', 'ANIM', 'ANMF', 'VP8 ', 'VP8L'];
	public const bool ORIENTS = true;

	/**
	 * Keeps the chunks in `KEEP`, with a `VP8X` chunk
	 * where needed and the EXIF data last
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
		$own     = $icc === null;
		$profile = $icc ?? '';
		$iccp    = $profile === '' ? '' : Layout::Riff->chunk('ICCP', $profile);
		$chunk   = $exif === '' ? '' : Layout::Riff->chunk('EXIF', $exif);

		// the RIFF chunk's length counts `WEBP` and the chunks after it
		rewind($handle);
		$head   = (string)fread($handle, 30);
		$length = Bytes::int(substr($head, 4, 4), little: true) - 4;
		$first  = substr($head, 12);

		// the flags of the old meta off, those of the new on
		$clear  = Vp8x::FLAGS['EXIF'] | Vp8x::FLAGS['XMP '];
		$clear |= $own === true ? 0 : Vp8x::FLAGS['ICCP'];
		$set    = $exif === '' ? 0 : Vp8x::FLAGS['EXIF'];
		$set   |= $iccp === '' ? 0 : Vp8x::FLAGS['ICCP'];
		$flags  = (Vp8x::flags($first) & ~$clear) | $set;

		fwrite($out, "RIFF\0\0\0\0WEBP");

		if (
			$set !== 0 ||
			str_starts_with($first, 'VP8X') === true
		) {
			$vp8x = Vp8x::create($first, $flags);
			fwrite($out, $vp8x . $iccp);
		}

		$splice = new Splice($handle, $out);

		Bytes::seek($handle, 12);

		foreach (Layout::Riff->spans($handle, $length) as $type => [$start, $end]) {
			$keep = match ($type) {
				'ICCP'  => $own,
				default => in_array($type, self::KEEP, true)
			};

			if ($keep === true) {
				$splice->copy($start, $end);
			}
		}

		$splice->write($chunk);
		$splice->flush();

		$length = (int)ftell($out) - 8;

		fseek($out, 4);
		fwrite($out, pack('V', $length));
	}
}
