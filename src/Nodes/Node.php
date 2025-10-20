<?php

namespace Kirby\Nodes;

use Kirby\Cms\HasSiblings;
use Kirby\Uuid\Uuid;
use Kirby\Toolkit\Str;

class Node
{
	use HasSiblings;

	protected array $nodes = [];

	public function __construct(
		protected string|int $id,
		protected Nodes $siblings,
		protected string|null $uuid = null,
		protected array $data = [],
		protected Node|null $parent = null,
	) {
		$this->uuid ??= Uuid::generate();
	}

	public function data(): array
	{
		return $this->data;
	}

	public function find(string $id): Nodes|Node|null
	{
		if (str_contains($id, '/') === true) {
			$ids   = Str::split($id, '/');
			$nodes = $this->nodes($ids[0]);

			return $nodes->find(implode('/', array_slice($ids, 1)));
		}

		return $this->nodes($id);
	}

	public static function from(
		array $data,
		string $id,
		Node|null $parent = null,
		Nodes|null $siblings = null,
		string|null $uuid = null,
	): static {
		return new static(
			data: $data,
			id: $id,
			parent: $parent,
			siblings: $siblings ?? new Nodes(),
			uuid: $uuid ?? $data['uuid'] ?? $data['id'] ?? null,
		);
	}

	public function id(): string
	{
		return $this->id;
	}

	public function nodes(string $id, string $format = 'auto'): Nodes
	{
		return $this->nodes[$id] ??= Nodes::from(
			data: $this->data[$id] ?? $this->data['content'][$id] ?? null,
			format: $format,
			id: $id,
			parent: $this,
		);
	}

	public function parent(): Node|null
	{
		return $this->parent;
	}

	public function path(): string
	{
		return $this->siblings->path() . '/' . $this->id();
	}

	protected function siblingsCollection(): Nodes
	{
		return $this->siblings;
	}

	public function toArray(): array
	{
		$nodes = [];

		foreach ($this->nodes as $id => $node) {
			$nodes[$id] = $node->toArray();
		}

		return [
			...$this->data,
			...$nodes,
			'uuid' => $this->uuid()
		];
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

