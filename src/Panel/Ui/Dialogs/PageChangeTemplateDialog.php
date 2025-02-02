<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Page;
use Kirby\Exception\Exception;
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
class PageChangeTemplateDialog extends FormDialog
{
	use IsForPage;

	public function __construct(
		public Page $page
	) {
		$blueprints = $this->page->blueprints();

		if (count($blueprints) <= 1) {
			throw new Exception(
				key: 'page.changeTemplate.invalid',
				data: ['slug' => $this->page->id()]
			);
		}

		parent::__construct(
			fields: [
				'notice' => [
					'type'  => 'info',
					'theme' => 'notice',
					'text'  => I18n::translate('page.changeTemplate.notice')
				],
				'template' => Field::template($blueprints, [
					'required' => true
				])
			],
			submitButton: [
				'text'  => I18n::translate('change'),
				'theme' => 'notice'
			],
			value: [
				'template' => $this->page->intendedTemplate()->name()
			]
		);
	}

	public function submit(): array
	{
		$template = $this->request->get('template');
		$this->page->changeTemplate($template);

		return [
			'event' => 'page.changeTemplate',
		];
	}
}
