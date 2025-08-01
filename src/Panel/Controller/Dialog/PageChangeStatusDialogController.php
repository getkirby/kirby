<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\ErrorDialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for changing the status of a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageChangeStatusDialogController extends PageDialogController
{
	public function fields(): array
	{
		$blueprint = $this->page->blueprint();
		$states    = [];

		foreach ($blueprint->status() as $key => $state) {
			$states[] = [
				'value' => $key,
				'text'  => $state['label'],
				'info'  => $state['text'],
			];
		}

		$fields = [
			'status' => [
				'label'    => I18n::translate('page.changeStatus.select'),
				'type'     => 'radio',
				'required' => true,
				'options'  => $states
			]
		];

		if ($blueprint->num() === 'default') {
			$fields['position'] = Field::pagePosition($this->page, [
				'when' => ['status' => 'listed']
			]);
		}

		return $fields;
	}

	public function load(): Dialog
	{
		if ($this->page->status() === 'draft') {
			$errors = $this->page->errors();

			// switch to the error dialog if there are
			// errors and the draft cannot be published
			if (count($errors) > 0) {
				return new ErrorDialog(
					message: I18n::translate('error.page.changeStatus.incomplete'),
					details: $errors,
				);
			}
		}

		return new FormDialog(
			fields: $this->fields(),
			submitButton: I18n::translate('change'),
			value: [
				'status'   => $this->page->status(),
				'position' => $this->position()
			]
		);
	}

	protected function position(): int|null
	{
		if ($this->page->blueprint()->num() === 'default') {
			return $this->page->panel()->position();
		}

		return null;
	}

	public function submit(): array|true
	{
		$this->page = $this->page->changeStatus(
			$this->request->get('status'),
			$this->request->get('position')
		);

		return [
			'event' => 'page.changeStatus',
		];
	}
}
