<?php

namespace Kirby\Cms;

use Exception;

class PageRules
{

    public static function changeNum(Page $page, int $num = null): bool
    {
        if ($num !== null && $num < 0) {
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
        if ($page->blueprint()->options()->changeStatus() !== true) {
            throw new Exception('The status for this page cannot be changed');
        }

        switch ($status) {
            case 'draft':
                return static::changeStatusToDraft($page);
            case 'listed':
                return static::changeStatusToListed($page, $position);
            case 'unlisted':
                return static::changeStatusToUnlisted($page);
            default:
                throw new Exception('Invalid status');
        }
    }

    public static function changeStatusToDraft(Page $page)
    {
        return true;
    }

    public static function changeStatusToListed(Page $page, int $position)
    {
        if ($position !== null && $position < 0) {
            throw new Exception('Invalid position');
        }

        if ($page->isDraft() === true && empty($page->errors()) === false) {
            throw new Exception('The page has errors and cannot be published');
        }

        return true;
    }

    public static function changeStatusToUnlisted(Page $page)
    {
        return true;
    }

    public static function changeTemplate(Page $page, string $template): bool
    {
        return true;
    }

    public static function changeTitle(Page $page, string $title): bool
    {
        return true;
    }

    public static function create(Page $page): bool
    {
        $siblings = $page->parentModel()->children();
        $drafts   = $page->parentModel()->drafts();
        $slug     = $page->slug();

        if ($duplicate = $siblings->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

        if ($duplicate = $drafts->find($slug)) {
            throw new Exception(sprintf('A draft with the URL appendix "%s" exists', $slug));
        }

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

    public static function update(Page $page, array $content = []): bool
    {
        return true;
    }

}
