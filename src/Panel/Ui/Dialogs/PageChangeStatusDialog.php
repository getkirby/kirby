<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;
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
class PageChangeStatusDialog
{
	use IsForPage;

	public PageBlueprint $blueprint;

	public function __construct(
		public Page $page
	) {
		$this->blueprint = $this->page->blueprint();
	}

	public function fields(): array
	{
		$fields = [
			'status' => [
				'label'    => I18n::translate('page.changeStatus.select'),
				'type'     => 'radio',
				'required' => true,
				'options'  => $this->states()
			]
		];

		if ($this->blueprint->num() === 'default') {
			$fields['position'] = Field::pagePosition($this->page, [
				'when' => [
					'status' => 'listed'
				]
			]);
		}

		return $fields;
	}

	public function render(): array
	{
		$status = $this->page->status();

		if ($status === 'draft') {
			$errors = $this->page->errors();

			// switch to the error dialog if there are
			// errors and the draft cannot be published
			if (count($errors) > 0) {
				return (new ErrorDialog(
					message: I18n::translate('error.page.changeStatus.incomplete'),
					details: $errors
				))->render();
			}
		}

		return (new FormDialog(
			fields: $this->fields(),
			submitButton: I18n::translate('change'),
			value: [
				'status'   => $status,
				'position' => match ($this->blueprint->num()) {
					'default' => $this->page->panel()->position(),
					default   => null
				}
			]
		))->render();
	}

	public function states(): array
	{
		$states = [];

		foreach ($this->blueprint->status() as $key => $state) {
			$states[] = [
				'value' => $key,
				'text'  => $state['label'],
				'info'  => $state['text'],
			];
		}

		return $states;
	}

	public function submit(): array
	{
		$request = App::instance()->request();

		$this->page->changeStatus(
			$request->get('status'),
			$request->get('position')
		);

		return [
			'event' => 'page.changeStatus',
		];
	}
}
