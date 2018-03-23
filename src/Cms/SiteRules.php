<?php

namespace Kirby\Cms;

use Exception;

class SiteRules
{

    public static function update(Site $site, array $content = []): bool
    {
        if ($site->permissions()->update() !== true) {
            throw new Exception('The site cannot be updated');
        }

        return true;
    }

}
