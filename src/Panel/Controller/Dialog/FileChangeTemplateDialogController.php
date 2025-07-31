<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for changing the template of a file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FileChangeTemplateDialogController extends FileDialogController
{
	public function load(): Dialog
	{
		$blueprints = $this->file->blueprints();

		return new FormDialog(
			fields: [
				'warning' => [
					'type'  => 'info',
					'theme' => 'notice',
					'text'  => I18n::translate('file.changeTemplate.notice')
				],
				'template' => Field::template($blueprints, [
					'required' => true
				])
			],
			submitButton: [
				'text' => I18n::translate('change'),
				'theme' => 'notice'
			],
			value: [
				'template' => $this->file->template()
			]
		);
	}

	public function submit(): array
	{
		$template   = $this->request->get('template');
		$this->file = $this->file->changeTemplate($template);

		return [
			'event' => 'file.changeTemplate',
		];
	}
}
