<?php

namespace Kirby\Model;

use Kirby\Uuid\Uuid;

class ModelMeta
{
	public function __construct(
		public string $identifier,
		public int $created = 0,
		public int $modified = 0,
		public string|null $uuid = null
	) {
		$this->uuid ??= Uuid::generate();
	}
}
