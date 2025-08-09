<?php

namespace Kirby\Model;

class User extends Model
{
	use UserActions;

	const STORAGE = UserStorage::class;

	protected UserMeta $meta;

	public function __construct(
		string $identifier,
		string $email,
		string|null $name = null,
		string|null $language = null,
		string|null $password = null,
		string $role = 'default',
		int $created = 0,
		int $modified = 0,
		string|null $uuid = null,
	) {
		$this->meta = new UserMeta(
			created: $created,
			email: $email,
			identifier: $identifier,
			language: $language,
			modified: $modified,
			name: $name,
			password: $password,
			role: $role,
			uuid: $uuid,
		);
	}

	public function id(): string
	{
		return $this->meta->uuid;
	}

	public function meta(): UserMeta
	{
		return $this->meta;
	}
}
