<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Panel\Model;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class PageItem extends ModelItem
{
	/**
	 * @var \Kirby\Cms\Page
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Kirby\Panel\Page
	 */
	protected Model $panel;

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
			'dragText'    => $this->dragText(),
			'parent'      => $this->model->parentId(),
			'status'      => $this->model->status(),
			'template'    => $this->model->intendedTemplate()->name(),
			'url'         => $this->model->url(),
		];
	}
}
