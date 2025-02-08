<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\File;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Dialog to delete a file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class FileDeleteDialog extends RemoveDialog
{
	use IsForFile;

	public function __construct(
		public File $file
	) {
		parent::__construct(
			text: I18n::template('file.delete.confirm', [
				'filename' => Escape::html($this->file->filename())
			]),
		);
	}

	public function submit(): array
	{
		$referrer = Panel::referrer();
		$url      = $this->file->panel()->url(true);

		$this->file->delete();

		// redirect to the parent model URL
		// if the dialog has been opened in the file view
		if ($referrer === $url) {
			$redirect = $this->file->parent()->panel()->url(true);
		}

		return [
			'event'    => 'file.delete',
			'redirect' => $redirect
		];
	}
}
