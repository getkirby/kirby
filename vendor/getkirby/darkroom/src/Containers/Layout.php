<?php

declare(strict_types = 1);

namespace Darkroom\Containers;

use Generator;

/**
 * How a container frames its chunks:
 * ISO BMFF boxes with a size that counts the header,
 * PNG chunks with a checksum after the data,
 * RIFF chunks with a little-endian size, padded to an even length
 *
 * @internal
 */
enum Layout
{
	case Box;
	case Png;
	case Riff;

	/**
	 * The chunk with its header and what follows its data
	 */
	public function chunk(string $type, string $data): string
	{
		$length = strlen($data);

		return match ($this) {
			self::Box  => pack('N', $length + 8) . $type . $data,
			self::Png  => pack('N', $length) . $type . $data . pack('N', crc32($type . $data)),
			self::Riff => $type . pack('V', $length) . $data . str_repeat("\0", $length % 2),
		};
	}

	/**
	 * @param Generator<string, int> $walk
	 */
	public static function find(Generator $walk, string $type): int
	{
		foreach ($walk as $found => $length) {
			if ($found === $type) {
				return $length;
			}
		}

		return 0;
	}

	/**
	 * Type, start and end of each chunk, from its header
	 * to the end of what follows its data, of all of them
	 *
	 * @param resource $handle
	 * @return Generator<string, array{int, int}>
	 */
	public function spans(
		$handle,
		int $length = PHP_INT_MAX
	): Generator {
		$end = (int)ftell($handle);

		foreach ($this->walk($handle, $length, PHP_INT_MAX) as $type => $size) {
			$start = $end;
			$end   = (int)ftell($handle) + $size + $this->tail($size);

			yield $type => [$start, $end];
		}
	}

	/**
	 * The bytes after a chunk's data
	 */
	public function tail(int $length): int
	{
		return match ($this) {
			self::Box  => 0,
			self::Png  => 4,
			self::Riff => $length % 2,
		};
	}

	/**
	 * Type and data length of each chunk, at most `$steps`
	 *
	 * @param resource $handle
	 * @return Generator<string, int>
	 */
	public function walk(
		$handle,
		int $length = PHP_INT_MAX,
		int $steps = Bytes::STEPS
	): Generator {
		$position = (int)ftell($handle);
		$end      = $position + min($length, PHP_INT_MAX - $position);
		$riff     = $this === self::Riff;
		$box      = $this === self::Box;
		$tail     = $this === self::Png ? 4 : 0;

		for ($step = 0; $step < $steps; $step++) {
			$head = (string)fread($handle, 8);

			if (strlen($head) < 8) {
				return;
			}

			/** @var array{1: int} $fields */
			$fields = unpack($riff === true ? 'V' : 'N', $head, $riff === true ? 4 : 0);
			$size   = $fields[1];

			// a box's size counts its header;
			// 0 runs to the end, 1 is followed by a 64-bit size
			if ($box === true) {
				$size = match ($size) {
					0       => $end - $position - 8,
					1       => Bytes::int((string)fread($handle, 8)) - 16,
					default => $size - 8
				};
			}

			$start = (int)ftell($handle);

			// beyond the enclosing box, or too large for an integer
			if ($size < 0 || $size > $end - $start) {
				return;
			}

			// as `tail()`, in the hot loop of every reader
			$position = $start + $size + ($riff === true ? $size % 2 : $tail);

			yield substr($head, $riff === true ? 0 : 4, 4) => $size;

			// nothing fits after it, as after a box that runs to the end
			if ($end - $position < 8) {
				return;
			}

			if (ftell($handle) !== $position) {
				fseek($handle, $position);
			}
		}
	}
}
