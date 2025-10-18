<?php

namespace Kirby\Permissions;

class LanguagesPermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
