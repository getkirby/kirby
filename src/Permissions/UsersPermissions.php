<?php

namespace Kirby\Permissions;

class UsersPermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
	) {
	}
}
