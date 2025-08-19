<?php

namespace Kirby\Permissions;

class LanguagesPermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
	) {
	}
}
