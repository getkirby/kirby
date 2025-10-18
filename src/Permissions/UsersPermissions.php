<?php

namespace Kirby\Permissions;

class UsersPermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
