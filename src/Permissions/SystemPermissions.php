<?php

namespace Kirby\Permissions;

class SystemPermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
