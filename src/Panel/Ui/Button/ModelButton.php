<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Ui\Button;
use Kirby\Toolkit\HasStringTemplate;

/**
 * UI button that belongs to a model
 * and can resolve string templates based on the model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class ModelButton extends Button
{
	use HasStringTemplate;

	public function __construct(
		public readonly ModelWithContent|null $model = null,
		...$attrs
	) {
		parent::__construct(...$attrs);
	}

	public function dialog(): string|null
	{
		return $this->stringTemplate($this->dialog);
	}

	public function drawer(): string|null
	{
		return $this->stringTemplate($this->drawer);
	}

	public function icon(): string|null
	{
		return $this->stringTemplate($this->icon);
	}

	public function link(): string|null
	{
		return $this->stringTemplate($this->link);
	}


	public function model(): ModelWithContent|null
	{
		return $this->model;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'dialog'  => $this->dialog(),
			'drawer'  => $this->drawer(),
			'icon'    => $this->icon(),
			'link'    => $this->link(),
			'text'    => $this->text(),
			'theme'   => $this->theme(),
			'title'   => $this->title(),
		];
	}

	public function text(): string|null
	{
		return $this->stringTemplateI18n($this->text);
	}

	public function theme(): string|null
	{
		return $this->stringTemplate($this->theme);
	}

	public function title(): string|null
	{
		return $this->stringTemplateI18n($this->title);
	}
}
