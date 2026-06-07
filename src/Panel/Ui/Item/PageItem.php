<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Page;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 *
 * @extends \Kirby\Panel\Ui\Item\ModelItem<\Kirby\Cms\Page, \Kirby\Panel\Page>
 */
class PageItem extends ModelItem
{
	public function __construct(
		Page $page,
		string|array|false|null $image = [],
		string|null $info = null,
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			model: $page,
			image: $image,
			info: $info,
			layout: $layout,
			text: $text ?? '{{ page.title }}',
		);
	}

	protected function dragText(): string
	{
		return $this->panel->dragText();
	}

	protected function permissions(): array
	{
		$permissions = $this->model->permissions();

		return [
			'changeSlug'   => $permissions->can('changeSlug'),
			'changeStatus' => $permissions->can('changeStatus'),
			'changeTitle'  => $permissions->can('changeTitle'),
			'delete'       => $permissions->can('delete'),
			'sort'         => $permissions->can('sort'),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'dragText' => $this->dragText(),
			'parent'   => $this->model->parentId(),
			'status'   => $this->model->status(),
			'template' => $this->model->intendedTemplate()->name(),
			'url'      => $this->model->url(),
		];
	}
}
