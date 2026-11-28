<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Jxl;

use Darkroom\Containers\Layout;
use Darkroom\Containers\Splice;
use Darkroom\Formats\Writer as Base;

/**
 * @internal
 */
final class Writer extends Base
{
	/**
	 * The boxes of the image
	 */
	protected const array KEEP = ['JXL ', 'ftyp', 'jxlc', 'jxli', 'jxll', 'jxlp'];

	/**
	 * Keeps the boxes in `KEEP`, an `Exif` box after
	 * the file type, wrapping a bare codestream for it
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
		$box    = $exif === '' ? '' : Layout::Box->chunk('Exif', pack('N', 0) . $exif);
		$splice = new Splice($handle, $out);

		rewind($handle);

		$head = (string)fread($handle, 12);

		rewind($handle);

		if ($head === Reader::SIGNATURE) {
			foreach (Layout::Box->spans($handle) as $type => [$start, $end]) {
				if (in_array($type, self::KEEP, true) === true) {
					$splice->copy($start, $end);
				}

				if ($type === 'ftyp') {
					$splice->write($box);
				}
			}

			$splice->flush();
			return;
		}

		// a bare codestream in a last box of size 0, to the end
		if ($box !== '' && str_starts_with($head, Codestream::SIGNATURE) === true) {
			$ftyp = Layout::Box->chunk('ftyp', 'jxl ' . pack('N', 0) . 'jxl ');
			fwrite($out, Reader::SIGNATURE . $ftyp . $box . pack('N', 0) . 'jxlc');
		}

		stream_copy_to_stream($handle, $out);
	}
}
