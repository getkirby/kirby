<?php

namespace Kirby\Panel\Controller\View;

use Override;

/**
 * Controls the view for a page file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PageFileViewController extends FileViewController
{
	#[Override]
	public function breadcrumb(): array
	{
		$breadcrumb = $this->model->parents()->flip()->values(
			fn ($parent) => [
				'label' => $parent->title()->toString(),
				'link'  => $parent->panel()->url(true),
			]
		);

		return [
			...$breadcrumb,
			...parent::breadcrumb(),
		];
	}
}
