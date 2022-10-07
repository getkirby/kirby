<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptors\Interceptor;

class Blueprint extends Interceptor
{
	public const CLASS_ALIAS = 'blueprint';

	protected $toArray = [
		'description',
		'fields',
		'isDefault',
		'name',
		'sections',
		'options',
		'tabs',
		'title',
	];

	public function allowedMethods(): array
	{
		return [
			'description',
			'field',
			'fields',
			'isDefault',
			'name',
			'options',
			'section',
			'sections',
			'tab',
			'tabs',
			'title',
		];
	}

	public function fields(): array
	{
		return $this->object->fields();
	}

	public function sections(): array
	{
		return array_keys($this->object->sections());
	}

	public function tab(string $name): ?array
	{
		if ($tab = $this->object->tab($name)) {
			foreach ($tab['columns'] as $columnIndex => $column) {
				$tab['columns'][$columnIndex]['sections'] = array_keys($column['sections']);
			}

			return $tab;
		}

		return null;
	}

	public function tabs(): array
	{
		$tabs = [];

		foreach ($this->object->tabs() as $tab) {
			$tabs[$tab['name']] = $this->tab($tab['name']);
		}

		return $tabs;
	}
}
