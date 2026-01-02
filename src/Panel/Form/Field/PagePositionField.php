<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\Page;
use Kirby\Form\Field\SelectField;

class PagePositionField extends SelectField
{
	public function __construct(
		protected Page $page,
		...$props
	) {
		parent::__construct(...$props);
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('page.changeStatus.position');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'position';
	}

	public function options(): array
	{
		$index    = 0;
		$options  = [];
		$siblings = $this->page->parentModel()->children()->listed()->not($this->page);

		foreach ($siblings as $sibling) {
			$index++;

			$options[] = [
				'value' => $index,
				'text'  => $index
			];

			$options[] = [
				'value'    => $sibling->id(),
				'text'     => $sibling->title()->value(),
				'disabled' => true
			];
		}

		$index++;

		$options[] = [
			'value' => $index,
			'text'  => $index
		];

		return $options;
	}

	public function type(): string
	{
		return 'select';
	}
}
