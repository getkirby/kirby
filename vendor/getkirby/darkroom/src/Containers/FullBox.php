<?php

declare(strict_types = 1);

namespace Darkroom\Containers;

/**
 * Reads the fields of an ISO BMFF full box
 *
 * @internal
 */
final class FullBox
{
	public int $position = 4;
	public readonly int $version;

	public function __construct(
		public readonly string $data
	) {
		$this->version = ord($data[0] ?? "\0");
	}

	public function bytes(int $length): string
	{
		$bytes = substr($this->data, $this->position, $length);
		$this->position += $length;

		return $bytes;
	}

	/**
	 * Packs `$value` into `$size` big-endian bytes
	 */
	public static function field(int $value, int $size): string|null
	{
		return match (true) {
			$value < 0  => null,
			$size === 0 => $value === 0 ? '' : null,
			$size === 2 => $value <= 0xFFFF ? pack('n', $value) : null,
			$size === 4 => $value <= 0xFFFFFFFF ? pack('N', $value) : null,
			$size === 8 => pack('J', $value),
			default     => null
		};
	}

	public function flags(): int
	{
		return Bytes::int(bytes: substr($this->data, 1, 3));
	}

	/**
	 * An ID or count, 16 bits in version 0, else 32
	 */
	public function id(): int
	{
		return $this->int(length: $this->width());
	}

	public function int(int $length): int
	{
		$bytes = substr($this->data, $this->position, $length);
		$this->position += $length;

		return $length < 8 ? (int)hexdec(bin2hex($bytes)) : Bytes::int($bytes);
	}

	/**
	 * The bytes after the position
	 */
	public function left(): int
	{
		return strlen($this->data) - $this->position;
	}

	/**
	 * The length of an ID or count:
	 * 32 bits from version `$from` on, else 16
	 */
	public function width(int $from = 1): int
	{
		return $this->version < $from ? 2 : 4;
	}
}
