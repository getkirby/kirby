<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\PagePicker;
use Kirby\Panel\Ui\Item\PageItem;

/**
 * Pagespicker field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PagespickerField extends ModelspickerField
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

	public function picker(): PagePicker
	{
		return new PagePicker([
			'image'    => $this->image(),
			'info'     => $this->info(),
			'layout'   => $this->layout(),
			'model'    => $this->model(),
			'page'     => $this->kirby()->api()->requestQuery('page'),
			'parent'   => $this->kirby()->api()->requestQuery('parent'),
			'query'    => $this->query(),
			'search'   => $this->kirby()->api()->requestQuery('search'),
			'subpages' => $this->subpages(),
			'text'     => $this->text()
		]);
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
