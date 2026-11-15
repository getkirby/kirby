<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Heif;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\FullBox;

/**
 * Item locations of an `iloc` box
 *
 * @internal
 */
final readonly class Iloc
{
	public int $base;
	public int $id;
	public int $index;
	public int $length;
	public int $method;
	public int $offset;

	/**
	 * @param $idat Where the data of the `idat` box starts
	 */
	public function __construct(
		public string $data,
		public int|null $idat = null
	) {
		$iloc  = new FullBox($data);
		$sizes = $iloc->int(2);

		$this->offset = $sizes >> 12;
		$this->length = $sizes >> 8 & 15;
		$this->base   = $sizes >> 4 & 15;

		// indexes and methods from version 1, 32-bit IDs from 2
		$this->index  = $iloc->version > 0 ? $sizes & 15 : 0;
		$this->method = $iloc->version > 0 ? 2 : 0;
		$this->id     = $iloc->width(2);
	}

	/**
	 * A cursor at the first entry, and the number of entries
	 *
	 * @return array{FullBox, int}
	 */
	protected function entries(): array
	{
		$cursor = new FullBox($this->data);
		$cursor->position = 6;
		$count = $cursor->int($this->id);
		return [$cursor, $count];
	}

	/**
	 * The next entry's item, construction method, data reference,
	 * base offset and number of extents
	 *
	 * @return array{int, int, int, int, int}
	 */
	protected function entry(FullBox $cursor): array
	{
		return [
			$cursor->int($this->id),
			$cursor->int($this->method) & 15,
			$cursor->int(2),
			$cursor->int($this->base),
			$cursor->int(2),
		];
	}

	/**
	 * Offset and length in the file of each item's extents
	 *
	 * @param array<int, string> $items
	 * @return array<int, list<array{int, int}>>
	 */
	public function extents(array $items): array
	{
		[$cursor, $entries] = $this->entries();

		$fields  = $this->index + $this->offset + $this->length;
		$extents = [];
		$steps   = 0;

		for (
			$entry = 0;
			$entry < $entries && $cursor->left() > 0;
			$entry++
		) {
			[$item, $stored, , $start, $count] = $this->entry($cursor);

			// in the file or in the `idat` box
			$origin = match ($stored) {
				0       => 0,
				1       => $this->idat,
				default => null
			};

			if ($origin === null || isset($items[$item]) === false) {
				$cursor->position += $count * $fields;
				continue;
			}

			// no more than the data holds, one without fields
			$count = match ($fields) {
				0       => min($count, 1),
				default => min($count, intdiv($cursor->left(), $fields))
			};

			$list  = [];
			$valid = true;

			for (
				$extent = 0;
				$extent < $count && $steps < Bytes::STEPS;
				$extent++, $steps++
			) {
				$cursor->position += $this->index;

				$from = $origin + $start + $cursor->int($this->offset);
				$size = $cursor->int($this->length);

				// before the start of the file or overflowed
				if ($from < 0 || is_int($from) === false) {
					$valid = false;
				} else {
					$list[] = [$from, $size];
				}
			}

			if ($valid === true) {
				$extents[$item] = $list;
			}
		}

		return $extents;
	}

	/**
	 * `iloc` box of the `meta` box
	 */
	public static function from(Meta $meta): static
	{
		$box = $meta->box('iloc');
		return new static($box->data, $meta->boxes['idat'][0] ?? null);
	}
}
