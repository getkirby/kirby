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

	/**
	 * Returns the icon, label and theme of the page's
	 * current status, as defined in its blueprint
	 */
	protected function flag(): array
	{
		$status = $this->model->blueprint()->status()[$this->model->status()] ?? [];

		return [
			'icon'  => $status['icon'] ?? null,
			'label' => $status['label'] ?? null,
			'theme' => $status['theme'] ?? null,
		];
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
			'flag'     => $this->flag(),
			'parent'   => $this->model->parentId(),
			'status'   => $this->model->status(),
			'template' => $this->model->intendedTemplate()->name(),
			'url'      => $this->model->url(),
		];
	}
}
