<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Page;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * Dialog to change the sort position of a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PageChangeSortDialog extends FormDialog
{
	use IsForPage;

	public function __construct(
		public Page $page
	) {
		if ($page->blueprint()->num() !== 'default') {
			throw new PermissionException(
				key: 'page.sort.permission',
				data: ['slug' => $this->page->slug()]
			);
		}

		parent::__construct(
			fields: [
				'position' => Field::pagePosition($page),
			],
			submitButton: I18n::translate('change'),
			value: [
				'position' => $this->page->panel()->position()
			]
		);
	}

	public function submit(): array
	{
		$position   = $this->request->get('position');
		$this->page = $this->page->changeStatus(
			'listed',
			$position
		);

		return [
			'event' => 'page.sort',
		];
	}
}
