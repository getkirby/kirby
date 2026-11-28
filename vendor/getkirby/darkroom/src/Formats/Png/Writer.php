<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Png;

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
	 * The ancillary chunks that change how the image looks or prints
	 */
	protected const array KEEP = [
		'acTL', 'cHRM', 'cICP', 'cLLI', 'fcTL', 'fdAT',
		'gAMA', 'iCCP', 'mDCV', 'pHYs', 'sRGB', 'tRNS'
	];
	public const bool ORIENTS = true;

	/**
	 * An `iCCP` chunk of the profile
	 */
	protected static function profile(string $profile): string
	{
		if (
			$profile === '' ||
			function_exists('gzcompress') === false
		) {
			return '';
		}

		$data = "icc\0\0" . (string)gzcompress($profile);
		return Layout::Png->chunk('iCCP', $data);
	}

	/**
	 * Keeps the critical chunks and those
	 * in `KEEP`; the meta after the header
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
		$own    = $icc === null;
		$chunk  = $exif === '' ? '' : Layout::Png->chunk('eXIf', $exif);
		$after  = $chunk . self::profile($icc ?? '');
		$splice = new Splice($handle, $out);

		$splice->copy(0, 8);
		Bytes::seek($handle, 8);

		foreach (Layout::Png->spans($handle) as $type => [$start, $end]) {
			// critical chunks start with an uppercase letter
			$keep = match (true) {
				(ord($type[0]) & 0x20) === 0 => true,
				$type === 'iCCP'             => $own,
				default                      => in_array($type, self::KEEP, true)
			};

			if ($keep === true) {
				$splice->copy($start, $end);
			}

			if ($type === 'IHDR') {
				$splice->write($after);
			}

			if ($type === 'IEND') {
				break;
			}
		}

		$splice->flush();
	}
}
