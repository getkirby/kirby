<?php

namespace Kirby\Permissions;

use Kirby\Permissions\Abstracts\PermissionsGroup;

class ModelsPermissions extends PermissionsGroup
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
	) {
	}
}
