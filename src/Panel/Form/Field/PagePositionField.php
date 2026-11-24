<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\Page;
use Kirby\Form\Field\SelectField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for selecting a page's position within its siblings
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PagePositionField extends SelectField
{
	#[Prop(computed: true)]
	protected string|null $name = 'position';

	public function __construct(
		protected Page $page,
		mixed ...$args
	) {
		parent::__construct(...$args);
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('page.changeStatus.position');
		}

		return parent::label();
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

			$options[] = match ($sibling->isListable()) {
				true  => [
					'value'    => $sibling->id(),
					'text'     => $sibling->title()->value(),
					'disabled' => true
				],
				false => [
					'value'    => '-' . $index,
					'text'     => '[' . $this->i18n('unknown') . ']',
					'disabled' => true
				]
			};
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
