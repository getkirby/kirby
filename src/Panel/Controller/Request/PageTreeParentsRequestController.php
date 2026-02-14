<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\Page;
use Kirby\Panel\Controller\RequestController;

/**
 * Returns the UUIDs/ids for all parents of the page
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class PageTreeParentsRequestController extends RequestController
{
	protected Page|null $page;
	protected bool $root;

	public function __construct()
	{
		parent::__construct();
		$this->page = $this->site->page($this->request->get('page'));
		$this->root = $this->request->get('root') === 'true';
	}

	public function load(): array
	{
		$parents   = $this->page?->parents()->flip();
		$parents   = $parents?->values(
			fn ($parent) => $parent->uuid()?->toString() ?? $parent->id()
		);
		$parents ??= [];

		if ($this->root === true) {
			array_unshift($parents, $this->site->uuid()?->toString() ?? '/');
		}

		return [
			'data' => $parents
		];
	}
}
