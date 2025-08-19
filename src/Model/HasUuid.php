<?php

namespace Kirby\Model;

trait HasUuid
{
	public function uuid(): string|null
	{
		return $this->meta->uuid ??= $this->storage()->read()['uuid'] ?? null;
	}
}
