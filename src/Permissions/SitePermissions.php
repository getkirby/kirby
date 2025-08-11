<?php

namespace Kirby\Permissions;

class SitePermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $changeTitle = null,
		public bool|null $read = null,
		public bool|null $update = null,
	) {
	}
}
