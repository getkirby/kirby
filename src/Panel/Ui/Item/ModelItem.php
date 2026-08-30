<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Model as Panel;
use Kirby\Panel\Ui\Item;
use Kirby\Toolkit\HtmlString;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 *
 * @template TModel of ModelWithContent
 * @template TPanel of panel
 */
class ModelItem extends Item
{
	/** @var TModel */
	protected ModelWithContent $model;

	/** @var TPanel */
	protected Panel $panel;

	/**
	 * @param TModel $model
	 */
	public function __construct(
		ModelWithContent $model,
		string|array|false|null $image = [],
		string|null $info = null,
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			text: $text ?? '{{ model.title }}',
			image: $image,
			info: $info,
			layout: $layout
		);

		$this->model = $model;
		/** @var TPanel */
		$this->panel = $model->panel();
	}

	protected function info(): string|HtmlString|null
	{
		// an empty template, not null: null would render the model id
		$info = $this->info ?? '';

		if ($this->layout === 'table') {
			return $this->model->toString($info);
		}

		return $this->model->toSafeHtmlString($info);
	}

	protected function image(): array|null
	{
		return $this->panel->image($this->image, $this->layout);
	}

	protected function link(): string
	{
		return $this->panel->url(true);
	}

	protected function permissions(): array
	{
		return $this->model->guards()->toArray();
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'id'          => $this->model->id(),
			'link'        => $this->link(),
			'permissions' => $this->permissions(),
			'uuid'        => $this->model->uuid()?->toString(),
		];
	}

	protected function text(): string|HtmlString
	{
		if ($this->layout === 'table') {
			return $this->model->toString($this->text);
		}

		return $this->model->toSafeHtmlString($this->text);
	}
}
