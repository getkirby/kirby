<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\File;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class FileChangeSortDialog extends FormDialog
{
	use IsForFile;

	public function __construct(
		public File $file
	) {
		parent::__construct(
			fields: [
				'position' => Field::filePosition($file)
			],
			submitButton: I18n::translate('change'),
			value: [
				'position' => match ($file->sort()->isEmpty()) {
					true  => $file->siblings(false)->count() + 1,
					false => $file->sort()->toInt()
				}
			]
		);
	}

	public function submit(): array
	{
		$files    = $this->file->siblings()->sorted();
		$ids      = $files->keys();
		$newIndex = (int)($this->request->get('position')) - 1;
		$oldIndex = $files->indexOf($this->file);

		array_splice($ids, $oldIndex, 1);
		array_splice($ids, $newIndex, 0, $this->file->id());

		$files->changeSort($ids);

		return [
			'event' => 'file.sort',
		];
	}
}
