<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Ui\View;

class BlueprintFieldsViewController extends BlueprintViewController
{
	public function breadcrumb(): array
	{
		return [
			...$this->parent()->breadcrumb(),
			[
				'label' => 'Fields',
				'link'  => $this->link(),
			]
		];
	}

	public function link(): string
	{
		return $this->parent()->link() . '/fields';
	}

	public function load(): View
	{
		return new View(
			component: 'k-blueprint-fields-view',
			breadcrumb: $this->breadcrumb(),
			fields: $this->fields()
		);
	}

	public function parent(): BlueprintViewController
	{
		return new BlueprintViewController($this->model);
	}
}
