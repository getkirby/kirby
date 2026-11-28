<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Heif;

use Darkroom\Containers\Layout;
use Darkroom\Formats\Writer as Base;

/**
 * @internal
 */
final class Writer extends Base
{
	/**
	 * Data and padding that overwrite each type of meta item
	 */
	protected const array BLANKS = [
		'Exif' => ["\0\0\0\0MM\0*\0\0\0\x08\0\0\0\0\0\0", "\0"],
		'mime' => ['<x:xmpmeta xmlns:x="adobe:ns:meta/"/>', ' '],
		'uri ' => ['', "\0"],
	];

	/**
	 * The top-level boxes of the image
	 */
	protected const array KEEP = ['ftyp', 'mdat', 'meta', 'moov'];

	/**
	 * Overwrites `$length` bytes at `$offset`
	 * with `$data`, then `$pad`
	 *
	 * @param resource $out
	 */
	protected static function blank(
		$out,
		int $offset,
		int $length,
		string $data,
		string $pad
	): void {
		if ($length < 1) {
			return;
		}

		if (strlen($data) > $length) {
			$data = '';
		}

		fseek($out, $offset);
		fwrite($out, $data);

		for (
			$left = $length - strlen($data);
			$left > 0;
			$left -= 8192
		) {
			fwrite($out, str_repeat($pad, min($left, 8192)));
		}
	}

	/**
	 * Copies the image with its meta blanked in place, as offsets
	 * point past it; false where `$exif` doesn't fit
	 *
	 * @param resource $out
	 */
	protected static function clean(
		Meta $meta,
		$out,
		string $exif = ''
	): bool {
		$handle  = $meta->handle;
		$types   = Items::types($meta);
		$items   = array_intersect($types, array_keys(self::BLANKS));
		$kept    = Items::auxiliary($meta, $items);
		$items   = array_diff_key($items, array_flip($kept));
		$iloc    = Iloc::from($meta);
		$extents = $iloc->extents($items);
		$own     = $exif === '' ? null : Items::describing($meta, 'Exif');
		$blanks  = [];
		$last    = 0;

		// in one extent of the image's own item
		if ($exif !== '') {
			$room = $own === null ? [] : $extents[$own] ?? [];

			if (count($room) !== 1 || strlen($exif) > $room[0][1]) {
				return false;
			}
		}

		// the data in the first extent, the padding in all
		foreach ($extents as $item => $list) {
			[$data, $pad] = self::BLANKS[$items[$item]];
			$data         = $item === $own ? $exif : $data;

			foreach ($list as [$offset, $length]) {
				$blanks[] = [$offset, $length, $data, $pad];
				$data     = '';
			}
		}

		rewind($handle);

		foreach (Layout::Box->spans($handle) as $type => [$start, $end]) {
			if (in_array($type, self::KEEP, true) === true) {
				$last = $end;
				continue;
			}

			// as a `free` box of zeros
			$from     = (int)ftell($handle);
			$blanks[] = [$start + 4, 4, 'free', ''];
			$blanks[] = [$from, $end - $from, '', "\0"];
		}

		rewind($handle);

		$copied = (int)stream_copy_to_stream($handle, $out, $last);

		foreach ($blanks as [$offset, $length, $data, $pad]) {
			self::blank($out, $offset, min($length, $copied - $offset), $data, $pad);
		}

		return true;
	}

	/**
	 * Copies the image without meta,
	 * with the EXIF data where it fits
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
		$data = $exif === '' ? '' : pack('N', 0) . $exif;

		rewind($handle);

		$meta = Meta::index($handle);

		if (self::clean($meta, $out, $data) === false) {
			self::clean($meta, $out);
		}
	}
}
