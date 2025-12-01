<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Field\BaseField;
use Kirby\Form\Form;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;
use Kirby\Reflection\Field;
use Kirby\Toolkit\A;

class BlueprintTabViewController extends ViewController
{
	public function __construct(
		protected ModelWithContent $model,
		protected array $tab
	) {
	}

	public function breadcrumb(): array
	{
		return [
			...$this->parent()->breadcrumb(),
			[
				'label' => 'Tabs',
			],
			[
				'label' => $this->tab['label'],
				'link'  => $this->link()
			]
		];
	}

	public function columns(): array
	{
		$columns = [];

		foreach ($this->tab['columns'] as $column) {

			$fields = [];

			foreach ($column['sections'] as $section) {
				if (isset($section['fields']) === true) {
					$fields = [...$fields, ...$section['fields']];
				} else {
					$fields[] = $section;
				}
			}

			$fields = A::map($fields, function ($field) {
				return [
					...$field,
					'link' => $this->parent()->link() . '/fields/' . $field['name']
				];
			});

			$columns[] = [
				'width' => $column['width'],
				'fields' => $fields,
			];
		}

		return $columns;
	}

	public static function factory(string $path, string $name): static
	{
		$model = Find::parent($path);

		return new static(
			model: $model,
			tab: $model->blueprint()->tab($name)
		);
	}

	public function link(): string
	{
		return $this->parent()->link() . '/tabs/' . $this->tab['name'];
	}

	public function load(): View
	{
		return new View(
			component: 'k-blueprint-tab-view',
			breadcrumb: $this->breadcrumb(),
			columns: $this->columns(),
			icon: $this->tab['icon'],
			label: $this->tab['label'],
			name: $this->tab['name'],
		);
	}

	public function parent(): BlueprintViewController
	{
		return new BlueprintViewController($this->model);
	}
}
