<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\PermissionException;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog for changing the sorting position of a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageChangeSortDialogController extends PageDialogController
{
	public function load(): Dialog
	{
		if ($this->page->blueprint()->num() !== 'default') {
			throw new PermissionException(
				key: 'page.sort.permission',
				data: ['slug' => $this->page->slug()]
			);
		}

		return new FormDialog(
			fields: [
				'position' => Field::pagePosition($this->page),
			],
			submitButton: $this->i18n('change'),
			value: [
				'position' => $this->page->panel()->position()
			]
		);
	}

	public function submit(): array|true
	{
		$this->page = $this->page->changeStatus(
			'listed',
			$this->request->get('position')
		);

		return [
			'event' => 'page.sort',
		];
	}
}
