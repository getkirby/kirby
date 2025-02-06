<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\Dialog;
use Kirby\Uuid\Uuids;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PageMoveDialog extends Dialog
{
	use IsForPage;

	public function __construct(
		public Page $page
	) {
		parent::__construct(
			component: 'k-page-move-dialog'
		);
	}

	public function props(): array
	{
		$parent = $this->page->parentModel();

		return [
			...parent::props(),
			'value' => [
				'move'   => $this->page->panel()->url(true),
				'parent' => match (Uuids::enabled()) {
					false   => $parent->id() ?? '/',
					default => $parent->uuid()->toString() ?? 'site://'
				}
			]
		];
	}

	public function submit(): array
	{
		$parent = $this->request->get('parent');
		$parent = match (true) {
			empty($parent) === true,
			$parent === '/',
			$parent === 'site://' => $this->kirby->site(),
			default               => Find::page($parent)
		};

		$page = $this->page->move($parent);

		return [
			'event'    => 'page.move',
			'redirect' => $page->panel()->url(true)
		];
	}
}
