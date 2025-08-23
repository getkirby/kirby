<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Toolkit\Escape;
use Override;

/**
 * Controls the Panel dialog for deleting a file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FileDeleteDialogController extends FileDialogController
{
	#[Override]
	public function load(): Dialog
	{
		return new RemoveDialog(
			text: $this->i18n('file.delete.confirm', [
				'filename' => Escape::html($this->file->filename())
			])
		);
	}

	#[Override]
	public function submit(): array
	{
		$referrer = $this->kirby->panel()->referrer();
		$url      = $this->file->panel()->url(true);

		$this->file->delete();

		// Redirect to the parent model URL
		// if the dialog has been opened in the file view
		if ($referrer === $url) {
			$redirect = $this->file->parent()->panel()->url(true);
		}

		return [
			'event'    => 'file.delete',
			'redirect' => $redirect ?? null
		];
	}
}
