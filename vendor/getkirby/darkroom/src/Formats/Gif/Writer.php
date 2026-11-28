<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Gif;

use Darkroom\Containers\Splice;
use Darkroom\Formats\Writer as Base;

/**
 * @internal
 */
final class Writer extends Base
{
	/**
	 * The application extensions for loops and the color profile
	 */
	protected const array KEEP = [
		'ANIMEXTS1.0',
		'ICCRGBG1012',
		'NETSCAPE2.0'
	];

	/**
	 * Keeps the header, images, graphic controls,
	 * plain text and the extensions in `KEEP`
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
		$splice = new Splice($handle, $out);
		$from   = null;
		$blocks = Reader::blocks($handle);

		// each part kept up to the start of the next
		foreach ($blocks as $kind => [$start, $head]) {
			if ($from !== null) {
				$splice->copy($from, $start);
			}

			$from = match ($kind) {
				'GIF',
				',',
				"!\x01",
				"!\xF9" => $start,
				"!\xFF" => in_array($head, self::KEEP, true) === true ? $start : null,
				default => null
			};
		}

		$splice->write(';');
		$splice->flush();
	}
}
