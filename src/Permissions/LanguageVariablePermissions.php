<?php

namespace Kirby\Permissions;

class LanguageVariablePermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $list = null,
		public bool|null $read = null,
		public bool|null $update = null,
	) {
	}
}
