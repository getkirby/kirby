<?php

declare(strict_types = 1);

namespace Darkroom\Formats\Heif;

use Darkroom\Containers\Bytes;
use Darkroom\Containers\FullBox;
use Darkroom\Containers\Layout;
use Generator;

/**
 * First box of each type in the `meta` box
 *
 * @internal
 */
final readonly class Meta
{
	/**
	 * @param resource $handle
	 * @param array<string, array{int, int}> $boxes Offset and length of the data by type
	 */
	protected function __construct(
		public mixed $handle,
		public array $boxes
	) {
	}

	/**
	 * Data of the box, at most `$length` bytes
	 */
	public function box(
		string $type,
		int $length = Bytes::LIMIT
	): FullBox {
		$box = $this->boxes[$type] ?? null;

		if ($box === null) {
			return new FullBox('');
		}

		Bytes::seek($this->handle, $box[0]);
		$data = Bytes::bytes($this->handle, min($box[1], $length));
		return new FullBox($data);
	}

	/**
	 * Type and data length of each box in the box, after `$skip` bytes
	 *
	 * @return Generator<string, int>
	 */
	public function boxes(string $type, int $skip = 0): Generator
	{
		$box = $this->boxes[$type] ?? null;

		if ($box === null) {
			return;
		}

		Bytes::seek($this->handle, $box[0] + $skip);
		yield from Layout::Box->walk($this->handle, $box[1] - $skip);
	}

	/**
	 * @param resource $handle
	 */
	public static function index($handle): static
	{
		$boxes = [];

		foreach (self::walk($handle) as $type => $length) {
			$boxes[$type] ??= [(int)ftell($handle), $length];
		}

		return new static($handle, $boxes);
	}

	/**
	 * Type and data length of each box in the `meta` box
	 *
	 * @param resource $handle
	 * @return Generator<string, int>
	 */
	public static function walk($handle): Generator
	{
		rewind($handle);

		$boxes  = Layout::Box->walk($handle);
		$length = Layout::find($boxes, 'meta');

		if ($length < 4) {
			return;
		}

		fseek($handle, 4, SEEK_CUR);

		yield from Layout::Box->walk($handle, $length - 4);
	}
}
