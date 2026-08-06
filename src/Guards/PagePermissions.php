<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Page;

/**
 * Role and blueprint based permissions for a `$page` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PagePermissions extends ModelPermissions
{
	/**
	 * @var Page
	 */
	protected Model $model;

	public function category(): string
	{
		return 'pages';
	}

	public function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'page.' . $key . '.permission',
			data: [
				'slug' => $this->model->slug(),
				...$data
			]
		);
	}

	/**
	 * Converting a page to a draft is covered
	 * by the `changeStatus` rule
	 */
	protected function ensureToChangeStatusToDraft(): void
	{
		$this->ensureSetting('changeStatus');
	}

	/**
	 * Publishing a page is covered by the `changeStatus` rule
	 */
	protected function ensureToPublish(): void
	{
		$this->ensureSetting('changeStatus');
	}
}
