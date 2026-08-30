<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Page;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 *
 * @extends ModelItem<Page, \Kirby\Panel\Page>
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
		$guards = $this->model->guards();

		return [
			'changeSlug'   => $guards->isAvailable('changeSlug'),
			'changeStatus' => $guards->isAvailable('changeStatus'),
			'changeTitle'  => $guards->isAvailable('changeTitle'),
			'delete'       => $guards->isAvailable('delete'),
			'sort'         => $guards->isAvailable('sort'),
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
