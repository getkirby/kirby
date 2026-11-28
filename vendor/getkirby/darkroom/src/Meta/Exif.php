<?php

declare(strict_types = 1);

namespace Darkroom\Meta;

use Darkroom\Containers\Bytes;
use Darkroom\Orientation;

/**
 * EXIF data, which has TIFF's layout in every container
 *
 * @internal
 */
final class Exif
{
	/**
	 * The tags in IFD0 that point to the other IFDs
	 */
	protected const array POINTERS = [
		'EXIF' => 0x8769,
		'GPS'  => 0x8825,
	];

	/**
	 * Bytes of each value of the TIFF types 1 to 13 and EXIF 3.0's UTF-8
	 */
	protected const array SIZES = [1 => 1, 1, 2, 4, 8, 1, 1, 2, 4, 8, 4, 8, 4, 129 => 1];

	/**
	 * Type, count and value of the entries of the tags
	 * in `$keep` in the IFD at the offset, without those
	 * of no type or with a value cut off
	 *
	 * @param array<int, mixed> $keep Tags as keys
	 * @return array<int, array{int, int, string}>
	 */
	protected static function entries(
		string $tiff,
		int $offset,
		bool $little,
		array $keep
	): array {
		$count   = Bytes::int(substr($tiff, $offset, 2), $little);
		$format  = $little === true ? 'vtag/vtype/Vcount' : 'ntag/ntype/Ncount';
		$entries = [];

		for ($index = 0; $index < $count; $index++) {
			$entry = substr($tiff, $offset + 2 + $index * 12, 12);

			if (strlen($entry) < 12) {
				break;
			}

			/** @var array{tag: int, type: int, count: int} $fields */
			$fields = unpack($format, $entry);

			if (isset($keep[$fields['tag']]) === false) {
				continue;
			}

			$length = $fields['count'] * (self::SIZES[$fields['type']] ?? 0);

			// in the entry, or at the offset it holds
			$value = match (true) {
				$length <= 4 => substr($entry, 8, $length),
				default      => substr($tiff, Bytes::int(substr($entry, 8, 4), $little), $length)
			};

			if ($length > 0 && strlen($value) === $length) {
				$entries[$fields['tag']] = [$fields['type'], $fields['count'], $value];
			}
		}

		return $entries;
	}

	/**
	 * TIFF data with those of the tags that the data has,
	 * and the orientation unless `None`; none without either
	 *
	 * @param list<int> $tags In any IFD, as their numbers differ
	 */
	public static function filter(
		string $tiff,
		array $tags,
		Orientation $orientation = Orientation::None
	): string {
		$little = self::little($tiff);
		$ifds   = $little === null ? [] : self::ifds($tiff, $little, $tags);

		if ($orientation !== Orientation::None) {
			$value = pack($little === true ? 'v' : 'n', $orientation->value);

			$ifds['IFD0'][0x0112] = [3, 1, $value];
		}

		return self::write($ifds, $little ?? false);
	}

	/**
	 * The entries of the tags by IFD, an EXIF IFD with its version
	 *
	 * @param list<int> $tags
	 * @return array<string, array<int, array{int, int, string}>>
	 */
	protected static function ifds(
		string $tiff,
		bool $little,
		array $tags
	): array {
		$keep   = array_flip([...$tags, 0x9000]);
		$offset = Bytes::int(substr($tiff, 4, 4), $little);
		$ifds   = ['IFD0' => self::entries($tiff, $offset, $little, $keep + array_flip(self::POINTERS))];

		foreach (self::POINTERS as $name => $pointer) {
			$offset = Bytes::int($ifds['IFD0'][$pointer][2] ?? '', $little);

			// as written, not as the data has it
			unset($ifds['IFD0'][$pointer]);

			if ($offset > 0) {
				$ifds[$name] = self::entries($tiff, $offset, $little, $keep);
			}
		}

		// the version only with other tags
		if (array_keys($ifds['EXIF'] ?? []) === [0x9000]) {
			unset($ifds['EXIF']);
		}

		return $ifds;
	}

	/**
	 * The byte order of TIFF data, none for other data
	 */
	protected static function little(string $data): bool|null
	{
		return match (substr($data, 0, 4)) {
			"II*\0" => true,
			"MM\0*" => false,
			default => null
		};
	}

	/**
	 * Moves past JPEG's `Exif\0\0` marker,
	 * if the data at the handle starts with it
	 *
	 * @param resource $handle
	 */
	public static function marker($handle): bool
	{
		$start = (int)ftell($handle);

		if (fread($handle, 6) === "Exif\0\0") {
			return true;
		}

		Bytes::seek($handle, $start);

		return false;
	}

	/**
	 * The orientation from the first IFD
	 * of the data at the handle
	 *
	 * @param resource $handle
	 */
	public static function orientation($handle): Orientation
	{
		$start = (int)ftell($handle);
		$head  = (string)fread($handle, 6);

		// after JPEG's marker, which some writers keep in other
		// containers; read on, as seeking back costs more
		if ($head === "Exif\0\0") {
			$start += 6;
			$head   = '';
		}

		$head       .= (string)fread($handle, 8 - strlen($head));
		$little      = self::little($head);
		$orientation = 1;

		if ($little !== null) {
			// counted from the start of the TIFF header
			$offset = Bytes::int(substr($head, 4), $little);
			Bytes::seek($handle, $start + $offset);

			// the number of entries, then 12 bytes each;
			// one cut off is left out
			$count   = Bytes::int((string)fread($handle, 2), $little);
			$entries = Bytes::bytes($handle, min($count, Bytes::STEPS) * 12);
			$format  = $little === true ? 'vtag/vtype/x4/Vvalue' : 'ntag/ntype/x4/Nvalue';

			for (
				$position = 0;
				$position + 12 <= strlen($entries);
				$position += 12
			) {
				/** @var array{tag: int, type: int, value: int} $entry */
				$entry = unpack($format, $entries, $position);

				if ($entry['tag'] !== 0x0112) {
					continue;
				}

				$value = $entry['value'];

				// a SHORT value fills the first two bytes, a LONG one all four
				$orientation = match ($entry['type']) {
					3       => $little === true ? $value & 0xFFFF : $value >> 16,
					4       => $value,
					default => $orientation
				};

				break;
			}
		}

		return Orientation::tryFrom($orientation) ?? Orientation::None;
	}

	/**
	 * All tags by section, through ext-exif
	 */
	public static function tags(string $data): array
	{
		if (
			$data === '' ||
			function_exists('exif_read_data') === false
		) {
			return [];
		}

		// a memory stream, as `data:` URLs need `allow_url_fopen`
		/** @var resource $stream */
		$stream = fopen('php://memory', 'r+b');
		fwrite($stream, $data);
		rewind($stream);

		$tags = @exif_read_data($stream, as_arrays: true);
		fclose($stream);

		if (is_array($tags) === false) {
			return [];
		}

		// what ext-exif adds about the stream itself
		unset($tags['FILE'], $tags['COMPUTED']);

		return $tags;
	}

	/**
	 * @param resource $handle
	 */
	public static function tiff(
		$handle,
		int $length,
		bool $offset = false
	): string {
		if ($offset === true) {
			$data    = Bytes::bytes($handle, 4);
			$skip    = Bytes::int($data);
			$length -= 4 + $skip;

			Bytes::seek($handle, (int)ftell($handle) + $skip);
		}

		$data = Bytes::bytes($handle, $length);

		// without JPEG's marker, which some writers keep
		return str_starts_with($data, "Exif\0\0") === true ? substr($data, 6) : $data;
	}

	/**
	 * TIFF data of the IFDs that have entries, IFD0 first and pointing
	 * to the others, then the values longer than their entries
	 *
	 * @param array<string, array<int, array{int, int, string}>> $ifds
	 */
	protected static function write(array $ifds, bool $little): string
	{
		$ifds = array_filter($ifds);

		if ($ifds === []) {
			return '';
		}

		$ifds     = ['IFD0' => [], ...$ifds];
		$short    = $little === true ? 'v' : 'n';
		$long     = $little === true ? 'V' : 'N';
		$pointers = count($ifds) - 1;
		$offsets  = [];
		$offset   = 8;

		// the number of entries, 12 bytes each, the next IFD's offset
		foreach ($ifds as $name => $entries) {
			$count          = count($entries) + ($name === 'IFD0' ? $pointers : 0);
			$offsets[$name] = $offset;
			$offset        += 6 + 12 * $count;
		}

		foreach (self::POINTERS as $name => $pointer) {
			if (isset($offsets[$name]) === true) {
				$ifds['IFD0'][$pointer] = [4, 1, pack($long, $offsets[$name])];
			}
		}

		$tiff   = ($little === true ? "II*\0" : "MM\0*") . pack($long, 8);
		$values = '';

		// in ascending order; values at even offsets
		foreach ($ifds as $entries) {
			ksort($entries);

			$tiff .= pack($short, count($entries));

			foreach ($entries as $tag => [$type, $count, $value]) {
				$field = str_pad($value, 4, "\0");

				if (strlen($value) > 4) {
					$field   = pack($long, $offset + strlen($values));
					$values .= $value . str_repeat("\0", strlen($value) % 2);
				}

				$tiff .= pack($short . $short . $long, $tag, $type, $count) . $field;
			}

			$tiff .= pack($long, 0);
		}

		return $tiff . $values;
	}
}
