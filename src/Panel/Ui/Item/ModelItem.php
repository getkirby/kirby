<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Model as Panel;
use Kirby\Panel\Ui\Component;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class ModelItem extends Component
{
	protected string $layout;
	protected Panel $panel;
	protected string $text;

	public function __construct(
		protected ModelWithContent $model,
		protected string|array|false|null $image = [],
		protected string|null $info = null,
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(component: 'k-item');

		$this->layout = $layout ?? 'list';
		$this->panel  = $this->model->panel();
		$this->text   = $text ?? '{{ model.title }}';
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
			'id'          => $this->model->id(),
			'image'       => $this->image(),
			'info'        => $this->info(),
			'link'        => $this->link(),
			'permissions' => $this->permissions(),
			'text'        => $this->text(),
			'uuid'        => $this->model->uuid()?->toString(),
		];
	}

	protected function text(): string
	{
		return $this->model->toSafeString($this->text);
	}
}
