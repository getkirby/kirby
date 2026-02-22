<?php

namespace Kirby\Cache;

function time(): int
{
	return \Kirby\Tests\time(1337);
}

class TestCache extends Cache
{
	public array $store = [];

	public function set(string $key, $value, int $minutes = 0, int|null $created = null): bool
	{
		$value = new Value($value, $minutes, $created);
		$this->store[$key] = $value;
		return true;
	}

	public function retrieve(string $key): Value|null
	{
		return $this->store[$key] ?? null;
	}

	public function remove(string $key): bool
	{
		unset($this->store[$key]);
		return true;
	}

	public function flush(): bool
	{
		$this->store = [];
		return true;
	}
}
