<?php

namespace Kirby\Panel\Controller\View;

/**
 * Controls the view for a user file
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserFileViewController extends FileViewController
{
	public function breadcrumb(): array
	{
		/** @var \Kirby\Cms\User $parent */
		$parent     = $this->model->parent();
		$breadcrumb = [];

		// The breadcrumb is not necessary
		// on the account view
		if ($parent->isLoggedIn() === false) {
			$breadcrumb[] = [
				'label' => $parent->username(),
				'link'  => $parent->panel()->url(true)
			];
		}

		return [
			...$breadcrumb ?? [],
			...parent::breadcrumb(),
		];
	}
}
