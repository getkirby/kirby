<?php

namespace Kirby\Model;

use Kirby\Cms\Collection;

class Page extends Model
{
	use HasNum;
	use HasTemplate;
	use HasTimestamps;
	use PageActions;

	const STORAGE = PageStorage::class;

	protected PageMeta $meta;
	protected Page|null $parent;

	public function __construct(
		string $identifier,
		string $slug,
		int $created = 0,
		int $modified = 0,
		int|null $num = null,
		PageParent|self|string|null $parent = null,
		PageStatus $status = PageStatus::Draft,
		string $template = 'default',
		string|null $uuid = null,
	) {
		$this->meta = new PageMeta(
			identifier: $identifier,
			slug: $slug,
			created: $created,
			modified: $modified,
			num: $num,
			parent: PageParent::from($parent),
			status: $status,
			template: $template,
			uuid: $uuid,
		);
	}

	public function children(): Collection
	{
		$children = $this->storage()->children();
		$children = array_map(fn($child) => Page::findByIdentifier($child), $children);

		return new Collection($children);
	}

	public function files(): Collection
	{
		$files = $this->storage()->files();
		$files = array_map(fn($file) => File::findByIdentifier($file), $files);

		return new Collection($files);
	}

	public function id(): string
	{
		return ltrim($this->parent()?->id() . '/' . $this->slug(), '/');
	}

	public function meta(): PageMeta
	{
		return $this->meta;
	}

	public function parent(): Page|null
	{
		return $this->meta->parent->load();
	}

	public function slug(): string
	{
		return $this->meta->slug;
	}

	public function status(): PageStatus
	{
		return $this->meta->status;
	}
}
