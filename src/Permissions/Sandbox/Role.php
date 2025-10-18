<?php

namespace Kirby\Permissions\Sandbox;

use Kirby\Permissions\KirbyPermissions;

class Role
{
	public function __construct(
		protected string $name
	) {
	}

	public function isAdmin(): bool
	{
		return $this->name === 'admin';
	}

	public function isKirby(): bool
	{
		return $this->name === 'kirby';
	}

	public function isNobody(): bool
	{
		return $this->name === 'nobody';
	}

	public function name(): string
	{
		return $this->name;
	}

	public function permissions(): KirbyPermissions
	{
		return match (true) {
			$this->isAdmin()  => KirbyPermissions::forAdmin(),
			$this->isNobody() => KirbyPermissions::forNobody(),

			// load permissions from role/user blueprint here
			default           => new KirbyPermissions()
		};
	}
}
