<?php

namespace Kirby\Model;

abstract class Model
{
	use HasTimestamps;
	use HasUuid;

	const STORAGE = Storage::class;

	public function content(): array
	{
		return $this->storage()->read();
	}

	public static function findByIdentifier(string $identifier): static|null
	{
		return static::STORAGE::find(static::class, $identifier);
	}

	abstract public function id(): string;

	abstract public function meta(): ModelMeta;

	public function storage(): Storage
	{
		$class = static::STORAGE;
		return new $class($this);
	}

	public function update(array $data): static
	{
		$this->storage()->update($data);
		return $this;
	}
}
