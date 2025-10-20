<?php

namespace Kirby\Nodes;

use Closure;
use Kirby\Cms\Collection;
use Kirby\Data\Data;
use Kirby\Data\Json;
use Kirby\Toolkit\Str;

class Nodes extends Collection
{
	public function __construct(
		protected string $id = '/',
		array $nodes = [],
		protected object|null $parent = null,
	) {
		foreach ($nodes as $name => $node) {
			$this->__set($name, $node);
		}
	}

	public function changeSort(array $ids): static
	{
		$nodes = [];

		if (count($ids) === 0) {
			return $this;
		}

		foreach ($ids as $id) {
			if ($node = $this->findByKey($id)) {
				$nodes[] = $node;
			}
		}

		if (count($nodes) === 0) {
			return $this;
		}

		foreach ($this as $node) {
			if (in_array($node, $nodes) === false) {
				$nodes[] = $node;
			}
		}

		$this->data = $nodes;

		return $this;
	}

	public function findByKey(string $key): Nodes|Node|null
	{
		if (str_contains($key, '/')) {
			return $this->findByKeyRecursive($key);
		}

		if ($node = parent::findByKey($key)) {
			return $node;
		}

		return $this->findBy('uuid', $key);
	}

	public function findByKeyRecursive(string $key): Nodes|Node|null
	{
		$ids = Str::split($key, '/');

		// search for the module by id
		$node = $this->findByKey($ids[0]);

		// no module found
		if ($node === null) {
			return null;
		}

		return $node->find(implode('/', array_slice($ids, 1)));
	}

	public static function from(
		array|string|null $data,
		string $id = '/',
		string $format = 'auto',
		Node|null $parent = null,
	): static {
		if (is_string($data) === true) {
			if ($format === 'auto') {
				$format = 'yaml';

				if (json_validate($data) === true) {
					$format = 'json';
				}
			}

			$data = Data::decode($data, $format);
		}

		$nodes = new static(
			id: $id,
			parent: $parent,
		);

		if ($data === null) {
			return $nodes;
		}

		foreach ($data as $id => $node) {
			$nodes->add(Node::from(
				data: $node,
				id: $id,
				parent: $parent,
				siblings: $nodes,
			));
		}

		return $nodes;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function parent(): Node|null
	{
		return $this->parent;
	}

	public function path(): string
	{
		return trim($this->parent?->path() . '/' . $this->id(), '/');
	}

	public function toArray(Closure|null $callback = null): array
	{
		if ($callback !== null) {
			return parent::toArray($callback);
		}

		$array = [];

		foreach ($this as $node) {
			$array[$node->id()] = $node->toArray();
		}

		return $array;
	}

	public function toJson(bool $pretty = true): string
	{
		return Json::encode($this->toArray(), pretty: $pretty);
	}
}
