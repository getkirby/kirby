<?php

namespace Kirby\Permissions;

class PanelPermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
