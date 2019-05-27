<?php

namespace Kirby\Cms;

use Kirby\Exception\PermissionException;

/**
 * Validators for all site actions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class SiteRules
{
    public static function changeTitle(Site $site, string $title): bool
    {
        if ($site->permissions()->changeTitle() !== true) {
            throw new PermissionException(['key' => 'site.changeTitle.permission']);
        }

        return true;
    }

    public static function update(Site $site, array $content = []): bool
    {
        if ($site->permissions()->update() !== true) {
            throw new PermissionException(['key' => 'site.update.permission']);
        }

        return true;
    }
}
