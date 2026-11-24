<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Panel\Controller\Dialog\PagePickerDialogController;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Reflection\Attributes\Prop;

/**
 * Pagepicker field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelPickerField<Page>
 */
class PagePickerField extends ModelPickerField
{
	/**
	 * Optionally include subpages of pages
	 */
	#[Prop]
	protected bool $subpages = true;

	public function dialogs(): array
	{
		return [
			'picker' => fn () => new PagePickerDialogController(...[
				'model'     => $this->model(),
				'hasSearch' => $this->search(),
				'image'     => $this->image(),
				'info'      => $this->info(),
				'max'       => $this->max(),
				'multiple'  => $this->multiple(),
				'query'     => $this->query(),
				'subpages'  => $this->subpages(),
				'text'      => $this->text(),
				...$this->picker()
			])
		];
	}

	public function subpages(): bool
	{
		return $this->subpages;
	}

	/**
	 * @param Page $model
	 */
	public function toItem(ModelWithContent $model): array
	{
		return (new PageItem(
			page:   $model,
			image:  $this->image(),
			info:   $this->info(),
			layout: $this->layout(),
			text:   $this->text()
		))->props();
	}

	public function toModel(string $id): Page|null
	{
		return $this->kirby()->page($id);
	}
}
