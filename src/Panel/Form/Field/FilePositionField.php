<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\File;
use Kirby\Form\Field\SelectField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for selecting a file's position within its siblings
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FilePositionField extends SelectField
{
	#[Prop(computed: true)]
	protected string|null $name = 'position';

	public function __construct(
		protected File $file,
		mixed ...$args
	) {
		parent::__construct(...$args);
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('file.sort');
		}

		return parent::label();
	}

	public function options(): array
	{
		$index   = 0;
		$options = [];

		foreach ($this->file->siblings(false)->sorted() as $sibling) {
			$index++;

			$options[] = [
				'value' => $index,
				'text'  => $index
			];

			$options[] = match ($sibling->isListable()) {
				true  => [
					'value'    => $sibling->id(),
					'text'     => $sibling->filename(),
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
