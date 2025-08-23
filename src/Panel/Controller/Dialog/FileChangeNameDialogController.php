<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Override;

/**
 * Controls the Panel dialog for changing the name of a file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FileChangeNameDialogController extends FileDialogController
{
	#[Override]
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'name' => [
					'label'     => $this->i18n('name'),
					'type'      => 'slug',
					'required'  => true,
					'icon'      => 'title',
					'allow'     => 'a-z0-9@._-',
					'after'     => '.' . $this->file->extension(),
					'preselect' => true
				]
			],
			submitButton: $this->i18n('rename'),
			value: [
				'name' => $this->file->name(),
			]
		);
	}

	#[Override]
	public function submit(): array
	{
		$name       = $this->request->get('name');
		$oldUrl     = $this->file->panel()->url(true);
		$this->file = $this->file->changeName($name);
		$newUrl     = $this->file->panel()->url(true);
		$response   = [
			'event' => 'file.changeName'
		];

		// Check for a necessary redirect after the filename has changed
		if (
			$this->kirby->panel()->referrer() === $oldUrl &&
			$oldUrl !== $newUrl
		) {
			$response['redirect'] = $newUrl;
		}

		return $response;
	}
}
