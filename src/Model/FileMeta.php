<?php

namespace Kirby\Model;

class FileMeta extends ModelMeta
{
	public function __construct(
		public string $filename,
		string $identifier,
		public FileParent $parent,
		public string $source,
		int $created = 0,
		int $modified = 0,
		public int|null $num = null,
		public string $template = 'default',
		string|null $uuid = null,
	) {
		parent::__construct(
			identifier: $identifier,
			created: $created,
			modified: $modified,
			uuid: $uuid,
		);
	}
}
