<?php

namespace Kirby\Guards;

use Kirby\Cms\Site;

/**
 * Role and blueprint based permissions for the `$site` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelPermissions<Site>
 */
class SitePermissions extends ModelPermissions
{
	public function category(): string
	{
		return 'site';
	}

	protected function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'site.' . $key . '.permission',
			data: $data
		);
	}
}
