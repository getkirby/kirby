<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Field\BaseField;
use Kirby\Form\Form;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;
use Kirby\Reflection\Field;

class BlueprintFieldViewController extends ViewController
{
	public function __construct(
		protected ModelWithContent $model,
		protected BaseField $field
	) {
	}

	public function breadcrumb(): array
	{
		return [
			...$this->parent()->breadcrumb(),
			[
				'label' => $this->field->name(),
				'link'  => $this->link()
			]
		];
	}

	public static function factory(string $path, string $name): static
	{
		$model = Find::parent($path);
		$form  = Form::for($model);
		$field = $form->field($name);

		return new static(
			model: $model,
			field: $field
		);
	}

	public function link(): string
	{
		return $this->parent()->link() . '/' . $this->field->name();
	}

	public function load(): View
	{
		$reflection = new Field($this->field);
		$props      = $reflection->props();
		$fieldprops = $this->field->props();

		foreach ($props as $name => $prop) {
			$props[$name]['value'] = $fieldprops[$name] ?? null;
		}

		return new View(
			component: 'k-blueprint-field-view',
			breadcrumb: $this->breadcrumb(),
			label: $this->field->label(),
			name: $this->field->name(),
			props: $props,
			type: $this->field->type(),
		);
	}

	public function parent(): BlueprintFieldsViewController
	{
		return new BlueprintFieldsViewController($this->model);
	}
}
