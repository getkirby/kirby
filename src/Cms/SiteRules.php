<?php

namespace Kirby\Cms;

use Exception;

class SiteRules
{

    public static function createChild(Site $site, Page $child): bool
    {
        $siblings = $site->children();
        $slug     = $child->slug();

        if ($duplicate = $siblings->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

        return true;
    }

    public static function createFile(Site $site, File $file): bool
    {
        return true;
    }

    public static function update(Site $site, array $content = []): bool
    {
        return true;
    }

}
