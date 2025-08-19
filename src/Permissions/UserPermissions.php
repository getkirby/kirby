<?php

namespace Kirby\Permissions;

class UserPermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $changeEmail = null,
		public bool|null $changeLanguage = null,
		public bool|null $changePassword = null,
		public bool|null $changeRole = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $list = null,
		public bool|null $read = null,
		public bool|null $update = null,
	) {
	}
}
