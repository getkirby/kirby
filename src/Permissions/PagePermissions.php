<?php

namespace Kirby\Permissions;

class PagePermissions extends Permissions
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
		public bool|null $preview = null,
		public bool|null $read = null,
		public bool|null $sort = null,
		public bool|null $update = null,
	) {
	}

	public static function forErrorPage(): static
	{
		return new static(
			changeSlug: false,
			changeStatus: false,
			changeTemplate: false,
			delete: false,
			move: false,
			sort: false
		);
	}

	public static function forHomePage(): static
	{
		return new static(
			changeSlug: false,
			delete: false,
			move: false,
		);
	}
}
