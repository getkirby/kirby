<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Heif;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\FullBox;

/**
 * Item tables: types and references
 *
 * @internal
 */
final class Items
{
	/**
	 * Items that describe only auxiliary images,
	 * such as gain maps
	 *
	 * @param array<int, string> $items
	 * @return list<int>
	 */
	public static function auxiliary(Meta $meta, array $items): array
	{
		$head      = $meta->box('iref', 4);
		$id        = $head->width();
		$auxiliary = [];
		$described = [];

		foreach ($meta->boxes('iref', 4) as $type => $size) {
			// from, number of references, to
			$data  = Bytes::bytes($meta->handle, $id + 2);
			$from  = Bytes::int(substr($data, 0, $id));
			$count = Bytes::int(substr($data, $id, 2));

			if ($type === 'auxl') {
				$auxiliary[] = $from;
			}

			if (
				$type === 'cdsc' &&
				isset($items[$from]) === true
			) {
				$targets = Bytes::bytes($meta->handle, min($count * $id, $size - $id - 2));
				$described[$from] = (array)unpack($id === 2 ? 'n*' : 'N*', $targets);
			}
		}

		$only = array_filter(
			$described,
			fn (array $targets) =>
				$targets !== [] &&
				array_diff($targets, $auxiliary) === []
		);

		return array_keys($only);
	}

	/**
	 * Whether the list holds the entry at a multiple of its length
	 */
	public static function contains(string $list, string $entry): bool
	{
		$offset = strpos($list, $entry);

		while ($offset !== false && $offset % strlen($entry) !== 0) {
			$offset = strpos($list, $entry, $offset + 1);
		}

		return $offset !== false;
	}

	/**
	 * The item of the type that describes the primary item
	 */
	public static function describing(
		Meta $meta,
		string $type
	): int|null {
		$types   = self::types($meta);
		$items   = array_keys($types, $type, true);
		$pitm    = $meta->box('pitm', 8);
		$primary = $pitm->id();

		return self::reference($meta, $items, $primary);
	}

	/**
	 * The item ID from a `pitm` box at the handle
	 *
	 * @param resource $handle
	 */
	public static function primary($handle, int $length): int
	{
		$data = Bytes::bytes($handle, min($length, 8));
		$pitm = new FullBox($data);

		return $pitm->id();
	}

	/**
	 * The first of the items with a `cdsc` reference to the item
	 *
	 * @param list<int> $items
	 */
	public static function reference(
		Meta $meta,
		array $items,
		int $item
	): int|null {
		$head  = $meta->box('iref', 4);
		$id    = $head->width();
		$entry = FullBox::field($item, $id);

		// an item too large for the references' IDs
		if ($entry === null) {
			return null;
		}

		foreach ($meta->boxes('iref', 4) as $type => $size) {
			if ($type !== 'cdsc') {
				continue;
			}

			// from, number of references, to
			$data  = Bytes::bytes($meta->handle, $id + 2);
			$from  = Bytes::int(substr($data, 0, $id));
			$count = Bytes::int(substr($data, $id, 2));

			if (in_array($from, $items, true) === false) {
				continue;
			}

			$length  = min($count * $id, $size - $id - 2);
			$targets = Bytes::bytes($meta->handle, $length);

			if (self::contains($targets, $entry) === true) {
				return $from;
			}
		}

		return null;
	}

	/**
	 * Type of each item by ID, from the `iinf` box
	 *
	 * @return array<int, string>
	 */
	public static function types(Meta $meta): array
	{
		// version and flags, then the number of entries
		$head  = $meta->box('iinf', 4);
		$skip  = 4 + $head->width();
		$items = [];

		foreach ($meta->boxes('iinf', $skip) as $type => $size) {
			$data = $type === 'infe' ? Bytes::bytes($meta->handle, min($size, 14)) : '';
			$infe = new FullBox($data);

			if ($data === '') {
				continue;
			}

			// 32-bit IDs from version 3; then the protection
			// index, and from version 2 the item type
			$item = $infe->int($infe->width(3));
			$infe->position += 2;
			$items[$item] = $infe->version >= 2 ? $infe->bytes(4) : '';
		}

		return $items;
	}
}
