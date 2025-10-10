<?php

namespace Kirby\Permissions;

class PagePermissions extends ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $changeSlug = null,
		public bool|null $changeStatus = null,
		public bool|null $changeTemplate = null,
		public bool|null $changeTitle = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $duplicate = null,
		public bool|null $list = null,
		public bool|null $move = null,
		public bool|null $read = null,
		public bool|null $sort = null,
		public bool|null $update = null,
	) {
	}
}
