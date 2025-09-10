<?php

namespace Kirby\Model;

class PagePermissions
{
	public function __construct(
		protected bool $access = false,
		protected bool $changeSlug = false,
		protected bool $changeStatus = false,
		protected bool $changeTemplate = false,
		protected bool $changeTitle = false,
		protected bool $create = false,
		protected bool $delete = false,
		protected bool $duplicate = false,
		protected bool $list = false,
		protected bool $move = false,
		protected bool $preview = false,
		protected bool $sort = false,
		protected bool $update = false,
	) {
	}
}
