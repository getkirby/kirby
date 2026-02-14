<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Controller\RequestController;
use Kirby\Panel\Ui\Item\ModelItem;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelItemsRequestController extends RequestController
{
	protected const ITEM_CLASS = ModelItem::class;

	protected function item(ModelWithContent $model): ModelItem
	{
		$class = static::ITEM_CLASS;
		return new $class(
			$model,
			image:  json_decode($this->request->get('image', '{}'), true),
			info:   $this->request->get('info'),
			layout: $this->request->get('layout'),
			text:   $this->request->get('text'),
		);
	}

	public function load(): array
	{
		$ids    = $this->request->get('items', '');
		$ids    = Str::split($ids);
		$models = A::map($ids, fn ($id) => $id ? $this->model($id) : null);
		$items  = A::map($models, fn ($model) => $model ? $this->item($model)->props() : null);
		return ['items' => $items];
	}

	abstract protected function model(string $id): ModelWithContent|null;
}
