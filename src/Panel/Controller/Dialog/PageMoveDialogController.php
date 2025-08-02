<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Uuid\Uuids;

/**
 * Controls the Panel dialog for moving a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageMoveDialogController extends PageDialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			component: 'k-page-move-dialog',
			value: [
				'move'   => $this->page->panel()->url(true),
				'parent' => $this->parent()
			]
		);
	}

	public function parent(): string
	{
		$parent = $this->page->parentModel();

		if (Uuids::enabled() === false) {
			return $parent?->id() ?? '/';
		}

		return $parent?->uuid()->toString() ?? 'site://';
	}

	public function submit(): array|true
	{
		$parent = $this->request->get('parent');

		if ($parent === '' || $parent === '/' || $parent === 'site://') {
			$parent = $this->kirby->site();
		} else {
			$parent = Find::page($parent);
		}

		$this->page = $this->page->move($parent);

		return [
			'event'    => 'page.move',
			'redirect' => $this->page->panel()->url(true)
		];
	}
}
