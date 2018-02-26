<?php

namespace Kirby\Cms;

use Exception;

class SiteRules
{

    public static function createFile(Site $site, File $file): bool
    {
        return true;
    }

    public static function update(Site $site, array $content = []): bool
    {
        return true;
    }

}
