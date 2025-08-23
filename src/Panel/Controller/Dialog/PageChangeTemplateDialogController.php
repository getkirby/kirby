<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\Exception;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Override;

/**
 * Controls the Panel dialog for changing the template of a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageChangeTemplateDialogController extends PageDialogController
{
	#[Override]
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

	#[Override]
	public function submit(): array|true
	{
		$template   = $this->request->get('template');
		$this->page = $this->page->changeTemplate($template);

		return [
			'event' => 'page.changeTemplate',
		];
	}
}
