<?php

namespace Kirby\Modules;

use Kirby\Cms\Block;
use Kirby\Cms\HasSiblings;
use Kirby\Cms\StructureObject;
use Kirby\Content\Field;
use Kirby\Uuid\Uuid;
use Kirby\Toolkit\Str;

class Module
{
	use HasSiblings;

	protected array $modules = [];

	public function __construct(
		protected string|int $id,
		protected Modules $siblings,
		protected string|null $uuid = null,
		protected array $data = [],
		protected Module|null $parent = null,
	) {
		$this->uuid ??= Uuid::generate();
	}

	public function data(): array
	{
		return $this->data;
	}

	public function find(string $id): Modules|Module|null
	{
		if (str_contains($id, '/') === true) {
			$ids     = Str::split($id, '/');
			$modules = $this->modules($ids[0]);

			return $modules->find(implode('/', array_slice($ids, 1)));
		}

		return $this->modules($id);
	}

	public static function from(
		array $data,
		string $id,
		Module|null $parent = null,
		Modules|null $siblings = null,
		string|null $uuid = null,
	): static {
		return new static(
			data: $data,
			id: $id,
			parent: $parent,
			siblings: $siblings ?? new Modules(),
			uuid: $uuid ?? $data['uuid'] ?? $data['id'] ?? null,
		);
	}

	public function id(): string
	{
		return $this->id;
	}

	public function modules(string $id, string $format = 'auto'): Modules
	{
		return $this->modules[$id] ??= Modules::from(
			data: $this->data[$id] ?? $this->data['content'][$id] ?? null,
			format: $format,
			id: $id,
			parent: $this,
		);
	}

	public function parent(): Module|null
	{
		return $this->parent;
	}

	public function path(): string
	{
		return $this->siblings->path() . '/' . $this->id();
	}

	protected function siblingsCollection(): Modules
	{
		return $this->siblings;
	}

	public function toArray(): array
	{
		$modules = [];

		foreach ($this->modules as $id => $module) {
			$modules[$id] = $module->toArray();
		}

		return [
			...$this->data,
			...$modules,
			'uuid' => $this->uuid()
		];
	}

	public function toBlockObject(Field $field): Block
	{
		return new Block([
			...$this->data,
			'field'  => $field,
			'id'     => $this->id(),
			'parent' => $field->model(),
		]);
	}

	public function toStructureObject(Field $field): StructureObject
	{
		return new StructureObject([
			'content' => $this->data,
			'field'   => $field,
			'id'      => $this->id(),
			'parent'  => $field->model(),
		]);
	}

	public function update(array $data): static
	{
		$this->data = [
			...$this->data,
			...$data,
		];

		return $this;
	}

	public function uuid(): string
	{
		return $this->uuid;
	}
}

