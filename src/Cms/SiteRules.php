<?php

namespace Kirby\Cms;

/**
 * Validators for all site actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @deprecated 6.0.0 Use `$site->guards()` instead
 */
class SiteRules
{
	public static function changeTitle(Site $site, string $title): void
	{
		$site->guards()->ensureExecutable('changeTitle', $title);
	}

	public static function update(Site $site, array $content = []): void
	{
		$site->guards()->ensureExecutable('update', $content);
	}
}
