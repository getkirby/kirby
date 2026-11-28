<?php

declare(strict_types = 1);

namespace Darkroom\Containers;

/**
 * Reads bytes and integers from image files,
 * with caps that keep a broken file from
 * exhausting memory or looping forever
 *
 * @internal
 */
final class Bytes
{
	public const int LIMIT = 1_048_576;
	public const int STEPS = 10_000;

	/**
	 * @param resource $handle
	 */
	public static function bytes($handle, int $length): string
	{
		if ($length < 1) {
			return '';
		}

		$length = min($length, self::LIMIT);

		return (string)stream_get_contents($handle, $length);
	}

	/**
	 * Copies from `$start` to `$end` (or to end of file)
	 *
	 * @param resource $handle
	 * @param resource $out
	 */
	public static function copy(
		$handle,
		$out,
		int $start,
		int $end
	): void {
		self::seek($handle, $start);
		stream_copy_to_stream($handle, $out, $end - $start);
	}

	public static function int(
		string $bytes,
		bool $little = false
	): int {
		if (strlen($bytes) === 1) {
			return ord($bytes);
		}

		if ($little === true) {
			$bytes = strrev($bytes);
		}

		// up to 7 bytes in hex, else the lowest 8
		if (strlen($bytes) < 8) {
			return (int)hexdec(bin2hex($bytes));
		}

		/** @var array{1: int} $value */
		$value = unpack('J', substr($bytes, -8));

		return $value[1];
	}

	/**
	 * @param resource $handle
	 */
	public static function seek($handle, int $position): void
	{
		if ((int)ftell($handle) !== $position) {
			fseek($handle, $position);
		}
	}
}
