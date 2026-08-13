<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Site;

/**
 * Role and blueprint based permissions for the `$site` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SitePermissions extends ModelPermissions
{
	/**
	 * @var Site
	 */
	protected Model $model;

	public function category(): string
	{
		return 'site';
	}

	public function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'site.' . $key . '.permission',
			data: $data
		);
	}
}
