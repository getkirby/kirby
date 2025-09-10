<?php

namespace Kirby\Model;

class UserPermissions
{
	public function __construct(
		protected bool $changeEmail = false,
		protected bool $changeLanguage = false,
		protected bool $changeName = false,
		protected bool $changePassword = false,
		protected bool $changeRole = false,
		protected bool $delete = false,
		protected bool $update = false,
	) {
	}
}
