<?php

namespace Kirby\Model;

use Kirby\Uuid\Uuid;

trait UserActions
{
	public function changeEmail(string $email): static
	{
		$this->meta = $this->storage()->changeMeta([
			'email' => $email,
		]);

		return $this;
	}

	public function changeLanguage(string $language): static
	{
		$this->meta = $this->storage()->changeMeta([
			'language' => $language,
		]);

		return $this;
	}

	public function changeName(string $name): static
	{
		$this->meta = $this->storage()->changeMeta([
			'name' => $name,
		]);

		return $this;
	}

	public function changePassword(string $password): static
	{
		$this->meta = $this->storage()->changeMeta([
			'password' => $password,
		]);

		return $this;
	}

	public function changeRole(string $role): static
	{
		$this->meta = $this->storage()->changeMeta([
			'role' => $role,
		]);

		return $this;
	}

	public static function create(
		string $email,
		string|null $language = null,
		string|null $name = null,
		string|null $password = null,
		string $role = 'default',
		string|null $uuid = null,
	): static {
		$meta = new UserMeta(
			email: $email,
			identifier: '__NEW__',
			language: $language,
			name: $name,
			password: $password,
			role: $role,
			uuid: $uuid,
		);

		return static::STORAGE::create($meta);
	}
}
