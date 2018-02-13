<?php

namespace Kirby\Cms;

use Exception;

class PageRules
{

    public static function changeNum(Page $page, int $num = null): bool
    {

        if ($num < 0) {
            throw new Exception('The page order number cannot be negative');
        }

        return true;
    }

    public static function changeSlug(Page $page, string $slug): bool
    {
        if ($page->isHomePage() === true) {
            throw new Exception('The slug of the home page cannot be changed');
        }

        if ($page->isErrorPage() === true) {
            throw new Exception('The slug of the error page cannot be changed');
        }

        if ($duplicate = $page->siblings()->not($page)->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

        return true;
    }

    public static function changeStatus(Page $page, string $status, int $position = null): bool
    {
        if (in_array($status, ['listed', 'unlisted']) === false) {
            throw new Exception(sprintf('Invalid status "%s"', $status));
        }

        return true;
    }

    public static function changeTemplate(Page $page, string $template): bool
    {
        return true;
    }

    public static function createChild(Page $page, Page $child): bool
    {
        $siblings = $page->children();
        $slug     = $child->slug();

        if ($duplicate = $siblings->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

        return true;
    }

    public static function createFile(Page $page, File $file): bool
    {
        return true;
    }

    public static function update(Page $page, array $content = []): bool
    {
        return true;
    }

    public static function delete(Page $page, bool $force = false): bool
    {
        if ($page->exists() === false) {
            throw new Exception('The page does not exist');
        }

        if ($page->hasChildren() === true && $force === false) {
            throw new Exception('The page has children');
        }

        if ($page->isHomePage()) {
            throw new Exception('The home page cannot be deleted');
        }

        if ($page->isErrorPage()) {
            throw new Exception('The error page cannot be deleted');
        }

        return true;
    }

}
