<?php

namespace Kirby\Permissions;

class SystemPermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
