<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\Exception;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog for changing the template of a page
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class PageChangeTemplateDialogController extends PageDialogController
{
	public function load(): Dialog
	{
		$blueprints = $this->page->blueprints();

		if (count($blueprints) <= 1) {
			throw new Exception(
				key: 'page.changeTemplate.invalid',
				data: ['slug' => $this->page->id()]
			);
		}

		return new FormDialog(
			fields: [
				'notice' => [
					'type'  => 'info',
					'theme' => 'notice',
					'text'  => $this->i18n('page.changeTemplate.notice')
				],
				'template' => Field::template($blueprints, [
					'required' => true
				])
			],
			submitButton: [
				'text'  => $this->i18n('change'),
				'theme' => 'notice'
			],
			value: [
				'template' => $this->page->intendedTemplate()->name()
			]
		);
	}

	public function submit(): array|true
	{
		$template   = $this->request->get('template');
		$this->page = $this->page->changeTemplate($template);

		return [
			'event' => 'page.changeTemplate',
		];
	}
}
