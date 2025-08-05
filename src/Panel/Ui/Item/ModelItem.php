<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Model as Panel;
use Kirby\Panel\Ui\Item;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class ModelItem extends Item
{
	protected Panel $panel;

	public function __construct(
		protected ModelWithContent $model,
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

		$this->panel = $this->model->panel();
	}

	protected function info(): string|null
	{
		return $this->model->toSafeString($this->info ?? false);
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
		return $this->model->permissions()->toArray();
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

	protected function text(): string
	{
		return $this->model->toSafeString($this->text);
	}
}
