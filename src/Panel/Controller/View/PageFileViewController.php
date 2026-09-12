<?php

namespace Kirby\Panel\Controller\View;

/**
 * Controls the view for a page file
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PageFileViewController extends FileViewController
{
	public function breadcrumb(): array
	{
		$breadcrumb = $this->model->parents()->flip()->values(
			fn ($parent) => $parent->panel()->crumb()
		);

		return [
			...$breadcrumb,
			...parent::breadcrumb(),
		];
	}
}
