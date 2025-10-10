<?php

namespace Kirby\Permissions;

use Kirby\Permissions\Abstracts\PermissionsGroup;

class AreaPermissions extends PermissionsGroup
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
