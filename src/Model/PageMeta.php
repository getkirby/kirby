<?php

namespace Kirby\Model;

class PageMeta extends ModelMeta
{
	public PageParent $parent;

	public function __construct(
		string $identifier,
		public string $slug,
		int $created = 0,
		int $modified = 0,
		public int|null $num = null,
		PageParent|null $parent = null,
		public PageStatus $status = PageStatus::Draft,
		public string $template = 'default',
		string|null $uuid = null,
	) {
		$this->parent = PageParent::from($parent);

		parent::__construct(
			identifier: $identifier,
			created: $created,
			modified: $modified,
			uuid: $uuid,
		);
	}
}
