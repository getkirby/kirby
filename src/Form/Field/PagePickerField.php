<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Panel\Controller\Dialog\PagePickerDialogController;
use Kirby\Panel\Ui\Item\PageItem;

/**
 * Pagepicker field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PagePickerField extends ModelPickerField
{
	/**
	 * Optionally include subpages of pages
	 */
	protected bool|null $subpages;

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $empty = null,
		array|string|null $help = null,
		array|null $image = null,
		string|null $info = null,
		array|string|null $label = null,
		string|null $layout = null,
		bool|null $link = null,
		int|null $max = null,
		int|null $min = null,
		bool|null $multiple = null,
		string|null $name = null,
		array|null $picker = null,
		string|null $query = null,
		bool|null $required = null,
		bool|null $search = null,
		string|null $size = null,
		string|null $store = null,
		bool|null $subpages = null,
		string|null $text = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			empty: $empty,
			help: $help,
			image: $image,
			info: $info,
			label: $label,
			layout: $layout,
			link: $link,
			max: $max,
			min: $min,
			multiple: $multiple,
			name: $name,
			picker: $picker,
			query: $query,
			required: $required,
			search: $search,
			size: $size,
			store: $store,
			text: $text,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->subpages = $subpages;
	}

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

	public function props(): array
	{
		return [
			...parent::props(),
			'subpages' => $this->subpages()
		];
	}

	public function subpages(): bool
	{
		return $this->subpages ?? true;
	}

	/**
	 * @param \Kirby\Cms\Page $model
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
