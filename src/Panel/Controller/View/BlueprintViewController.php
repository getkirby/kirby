<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Blueprint;
use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Form;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;

class BlueprintViewController extends ViewController
{
	public function __construct(
		protected ModelWithContent $model
	) {
	}

	public function blueprint(): Blueprint
	{
		return $this->model->blueprint();
	}

	public function breadcrumb(): array
	{
		return [
			...$this->parent()->breadcrumb(),
			[
				'label' => 'Blueprint',
				'link'  => $this->link()
			],
		];
	}

	public static function factory(string $path): static
	{
		$model = Find::parent($path);

		return new static(
			model: $model
		);
	}

	public function fields()
	{
		$link = $this->model->panel()->url(true) . '/blueprint/fields';

		return $this->form()->fields()->values(function ($field) use ($link) {
			return [
				'name'  => $field->name(),
				'label' => [
					'text' => $field->label(),
					'href' => $link . '/' . $field->name()
				],
				'type'     => $field->type(),
				'link'     => $link . '/' . $field->name(),
				'required' => $field->isRequired(),
			];
		});
	}

	public function form(): Form
	{
		return Form::for($this->model);
	}

	public function link(): string
	{
		return $this->model->panel()->url(true) . '/blueprint';
	}

	public function load(): View
	{
		$blueprint = $this->blueprint();

		return new View(
			component: 'k-blueprint-view',
			breadcrumb: $this->breadcrumb(),
			fields: $this->fields(),
			id: $blueprint->name(),
			icon: $blueprint->icon(),
			link: $this->link(),
			name: basename($blueprint->name()),
			tabs: $this->tabs(),
			title: $blueprint->title(),
		);
	}

	/**
	 * @return \Kirby\Panel\Controller\View\ModelViewController
	 */
	public function parent(): ViewController
	{
		$controller = 'Kirby\\Panel\\Controller\\View\\' . ucfirst($this->model::CLASS_ALIAS) . 'ViewController';
		return new $controller($this->model);
	}

	public function tabs(): array
	{
		return $this->blueprint()->tabs();
	}
}
