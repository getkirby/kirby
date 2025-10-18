<?php

namespace Kirby\Permissions;

class FilePermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $changeName = null,
		public bool|null $changeTemplate = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $list = null,
		public bool|null $read = null,
		public bool|null $replace = null,
		public bool|null $sort = null,
		public bool|null $update = null,
	) {
	}
}
