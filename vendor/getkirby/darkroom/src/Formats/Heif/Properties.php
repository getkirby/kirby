<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Heif;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\FullBox;
use Darkroom\Containers\Layout;

/**
 * Properties of the primary item
 *
 * @internal
 */
final class Properties
{
	/**
	 * Item's property indexes from an `ipma` box
	 *
	 * @param resource $handle
	 * @return list<int>|null
	 */
	public static function associations(
		$handle,
		int $length,
		int $item
	): array|null {
		$data     = Bytes::bytes($handle, $length);
		$ipma     = new FullBox($data);
		$entries  = $ipma->int(4);
		$id       = $ipma->width();
		$entry    = $id === 2 ? 'nitem/Ccount' : 'Nitem/Ccount';
		$position = $ipma->position;
		$end      = strlen($data) - $id;

		// flag 1 with 16-bit indexes, else 8-bit ones
		$size = ($ipma->flags() & 1) === 1 ? 2 : 1;
		$mask = $size === 2 ? 0x7FFF : 0x7F;

		// ID, number of indexes, indexes
		for (
			$step = 0;
			$step < $entries && $position < $end;
			$step++
		) {
			/** @var array{item: int, count: int} $fields */
			$fields    = unpack($entry, $data, $position);
			$position += $id + 1;

			if ($fields['item'] === $item) {
				$list = substr($data, $position, $fields['count'] * $size);

				/** @var array<int, int> $indexes */
				$indexes = unpack($size === 2 ? 'n*' : 'C*', $list);

				// without the top bit, which marks essential properties
				$masked = array_map(
					fn (int $index) => $index & $mask,
					$indexes
				);

				return array_values($masked);
			}

			$position += $fields['count'] * $size;
		}

		return null;
	}

	/**
	 * Primary item's properties of the types, in the order they apply
	 *
	 * @param resource $handle
	 * @param list<string> $types
	 * @param $bytes Read of each
	 * @return list<array{string, string}>
	 */
	public static function of(
		$handle,
		array $types,
		int $bytes = 32
	): array {
		$primary = 0;
		$iprp    = null;

		// in one walk, as this runs for every header
		foreach (Meta::walk($handle) as $type => $length) {
			match ($type) {
				'iprp'  => $iprp    = [(int)ftell($handle), $length],
				'pitm'  => $primary = Items::primary($handle, $length),
				default => null
			};

			if ($iprp !== null && $primary > 0) {
				break;
			}
		}

		if ($iprp === null) {
			return [];
		}

		$found   = null;
		$indexes = null;
		$boxes   = 0;

		// the first `ipco`, and the first `ipma`
		// that lists the item, of at most four
		Bytes::seek($handle, $iprp[0]);

		foreach (Layout::Box->walk($handle, $iprp[1]) as $type => $length) {
			if ($type === 'ipma' && ++$boxes > 4) {
				continue;
			}

			match ($type) {
				'ipco'  => $found   ??= self::properties($handle, $length, $types, $bytes),
				'ipma'  => $indexes ??= self::associations($handle, $length, $primary),
				default => null
			};
		}

		$properties = [];

		foreach ($indexes ?? [] as $index) {
			if (isset($found[$index]) === true) {
				$properties[] = $found[$index];
			}
		}

		return $properties;
	}

	/**
	 * Properties of the types in an `ipco` box, by index from 1
	 *
	 * @param resource $handle
	 * @param list<string> $types
	 * @return array<int, array{string, string}>
	 */
	protected static function properties(
		$handle,
		int $length,
		array $types,
		int $bytes
	): array {
		$found = [];
		$index = 0;

		foreach (Layout::Box->walk($handle, $length) as $type => $size) {
			$index++;

			if (in_array($type, $types, true) === true) {
				$found[$index] = [$type, Bytes::bytes($handle, min($size, $bytes))];
			}
		}

		return $found;
	}
}
