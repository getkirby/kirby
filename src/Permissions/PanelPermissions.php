<?php

namespace Kirby\Permissions;

class PanelPermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
