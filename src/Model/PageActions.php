<?php

namespace Kirby\Model;

trait PageActions
{
	public function changeSlug(string $slug): static
	{
		$this->meta = $this->storage()->changeMeta([
			'slug' => $slug,
		]);

		return $this;
	}

	public function changeStatus(PageStatus $status): static
	{
		$this->meta = $this->storage()->changeMeta([
			'status' => $status,
		]);

		return $this;
	}

	public static function create(
		string $slug,
		PageParent|self|string|null $parent = null,
		PageStatus $status = PageStatus::Draft,
		string $template = 'default',
		string|null $uuid = null,
	): static {
		$meta = new PageMeta(
			identifier: '__NEW__',
			slug: $slug,
			parent: PageParent::from($parent),
			status: $status,
			template: $template,
			num: null,
			uuid: $uuid,
		);

		return static::STORAGE::create($meta);
	}
}
