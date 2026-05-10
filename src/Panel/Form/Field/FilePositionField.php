<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\File;
use Kirby\Form\Field\SelectField;

/**
 * Panel field override for selecting a file's position within its siblings
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FilePositionField extends SelectField
{
	public function __construct(
		protected File $file,
		...$props
	) {
		parent::__construct(...$props);
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('file.sort');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'position';
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

			$options[] = [
				'value'    => $sibling->id(),
				'text'     => $sibling->filename(),
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
