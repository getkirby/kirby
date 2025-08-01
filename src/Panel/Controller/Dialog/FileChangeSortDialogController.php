<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for changing the sorting number of a file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FileChangeSortDialogController extends FileDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'position' => Field::filePosition($this->file)
			],
			submitButton: I18n::translate('change'),
			value: [
				'position' => $this->file->sort()->isEmpty() ?
					$this->file->siblings(false)->count() + 1 :
					$this->file->sort()->toInt(),
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
