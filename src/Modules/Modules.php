<?php

namespace Kirby\Modules;

use Closure;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\Structure;
use Kirby\Content\Field;
use Kirby\Data\Data;
use Kirby\Data\Json;
use Kirby\Toolkit\Str;

class Modules extends Collection
{
	public function __construct(
		protected string $id = '/',
		array $modules = [],
		protected object|null $parent = null,
	) {
		foreach ($modules as $name => $module) {
			$this->__set($name, $module);
		}
	}

	public function changeSort(array $ids): static
	{
		$modules = [];

		if (count($ids) === 0) {
			return $this;
		}

		foreach ($ids as $id) {
			if ($module = $this->findByKey($id)) {
				$modules[] = $module;
			}
		}

		if (count($modules) === 0) {
			return $this;
		}

		foreach ($this as $module) {
			if (in_array($module, $modules) === false) {
				$modules[] = $module;
			}
		}

		$this->data = $modules;

		return $this;
	}

	public function findByKey(string $key): Modules|Module|null
	{
		if (str_contains($key, '/')) {
			return $this->findByKeyRecursive($key);
		}

		if ($module = parent::findByKey($key)) {
			return $module;
		}

		return $this->findBy('uuid', $key);
	}

	public function findByKeyRecursive(string $key): Modules|Module|null
	{
		$ids = Str::split($key, '/');

		// search for the module by id
		$module = $this->findByKey($ids[0]);

		// no module found
		if ($module === null) {
			return null;
		}

		return $module->find(implode('/', array_slice($ids, 1)));
	}

	public static function from(
		array|string|null $data,
		string $id = '/',
		string $format = 'auto',
		Module|null $parent = null,
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

		$modules = new static(
			id: $id,
			parent: $parent,
		);

		if ($data === null) {
			return $modules;
		}

		foreach ($data as $id => $module) {
			$modules->add(Module::from(
				data: $module,
				id: $id,
				parent: $parent,
				siblings: $modules,
			));
		}

		return $modules;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function parent(): Module|null
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

		foreach ($this as $module) {
			$array[$module->id()] = $module->toArray();
		}

		return $array;
	}

	public function toBlocks(Field $field): Blocks
	{
		return new Blocks(
			$this->map(fn (Module $module) => $module->toBlockObject($field)),
			[
				'field'  => $field,
				'parent' => $field->model(),
			]
		);
	}

	public function toJson(bool $pretty = true): string
	{
		return Json::encode($this->toArray(), pretty: $pretty);
	}

	public function toStructure(Field $field): Structure
	{
		return new Structure(
			$this->map(fn (Module $module) => $module->toStructureObject($field)),
			[
				'field'  => $field,
				'parent' => $field->model(),
			]
		);
	}
}
