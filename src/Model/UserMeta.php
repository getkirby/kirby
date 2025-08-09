<?php

namespace Kirby\Model;

class UserMeta extends ModelMeta
{
	public function __construct(
		public string $identifier,
		public string|null $email = null,
		int $created = 0,
		public string|null $language = null,
		int $modified = 0,
		public string|null $name = null,
		public string|null $password = null,
		public string $role = 'default',
		string|null $uuid = null,
	) {
		parent::__construct(
			identifier: $identifier,
			created: $created,
			modified: $modified,
			uuid: $uuid,
		);
	}
}

