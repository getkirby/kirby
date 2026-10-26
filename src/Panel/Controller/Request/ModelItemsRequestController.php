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

	/**
	 * Presentation hints for the thumbnail image
	 */
	protected function image(): array
	{
		$settings = json_decode($this->request->get('image', '{}'), true);

		if (is_array($settings) === false) {
			return [];
		}

		$image = [];

		if (isset($settings['cover']) === true) {
			$image['cover'] = $settings['cover'] === true;
		}

		// accept a literal ratio (a number or `width/height`) only,
		// never a query that `Panel\Model::image()` would resolve
		if (
			isset($settings['ratio']) === true &&
			is_string($settings['ratio']) === true &&
			preg_match('!^\d+(\.\d+)?(/\d+(\.\d+)?)?$!', $settings['ratio']) === 1
		) {
			$image['ratio'] = $settings['ratio'];
		}

		return $image;
	}

	protected function item(ModelWithContent $model): ModelItem
	{
		$class = static::ITEM_CLASS;

		// the text and info templates are not read from the request:
		// they would be evaluated as queries against the model
		return new $class(
			$model,
			image:  $this->image(),
			layout: $this->request->get('layout'),
		);
	}

	public function load(): array
	{
		$ids    = $this->request->get('items', '');
		$ids    = Str::split($ids);
		$models = A::map($ids, fn ($id) => $id ? $this->model($id) : null);
		$items  = A::map($models, fn ($model) => $model?->isListable() === true ? $this->item($model)->props() : null);
		return ['items' => $items];
	}

	abstract protected function model(string $id): ModelWithContent|null;
}
