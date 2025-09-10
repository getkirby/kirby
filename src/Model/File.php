<?php

namespace Kirby\Model;

use Kirby\Filesystem\F;

class File extends Model
{
	use HasNum;
	use HasTemplate;
	use FileActions;

	const STORAGE = FileStorage::class;

	protected FileMeta $meta;

	public function __construct(
		string $filename,
		string $identifier,
		FileParent $parent,
		string $source,
		int $created = 0,
		int $modified = 0,
		int|null $num = null,
		string $template = 'default',
		string|null $uuid = null,
	) {
		$this->meta = new FileMeta(
			identifier: $identifier,
			filename: $filename,
			created: $created,
			modified: $modified,
			num: $num,
			parent: $parent,
			source: $source,
			template: $template,
			uuid: $uuid,
		);
	}

	public function extension(): string
	{
		return F::extension($this->filename());
	}

	public function filename(): string
	{
		return $this->meta->filename;
	}

	public function id(): string
	{
		return $this->parent()->id() . '/' . $this->filename();
	}

	public function meta(): FileMeta
	{
		return $this->meta;
	}

	public function name(): string
	{
		return F::name($this->filename());
	}

	public function parent(): Model
	{
		return $this->meta->parent->load();
	}
}
