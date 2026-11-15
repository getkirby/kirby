<?php

declare(strict_types = 1);

namespace Darkroom\Containers;

/**
 * Copies spans of a stream and writes data in order
 *
 * @internal
 */
final class Splice
{
	/**
	 * @var array{int, int}|null
	 */
	protected array|null $run = null;

	/**
	 * @param resource $handle
	 * @param resource $out
	 */
	public function __construct(
		protected $handle,
		protected $out
	) {
	}

	public function copy(int $start, int $end): void
	{
		if ($this->run !== null && $this->run[1] === $start) {
			$this->run[1] = $end;
			return;
		}

		$this->flush();
		$this->run = [$start, $end];
	}

	/**
	 * Copies what is left to copy
	 */
	public function flush(): void
	{
		if ($this->run !== null) {
			Bytes::copy($this->handle, $this->out, ...$this->run);
			$this->run = null;
		}
	}

	public function write(string $data): void
	{
		if ($data !== '') {
			$this->flush();
			fwrite($this->out, $data);
		}
	}
}
