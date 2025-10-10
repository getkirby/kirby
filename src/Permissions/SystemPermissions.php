<?php

namespace Kirby\Permissions;

class SystemPermissions extends AreaPermissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
