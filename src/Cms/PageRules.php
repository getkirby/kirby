<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\PermissionException;

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
        if ($page->permissions()->changeSlug() !== true) {
            throw new Exception('The slug for this page cannot be changed');
        }

        if ($duplicate = $page->siblings()->not($page)->find($slug)) {
            throw new Exception(sprintf('The URL appendix "%s" exists', $slug));
        }

        return true;
    }

    public static function changeStatus(Page $page, string $status, int $position = null): bool
    {
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
        if ($page->permissions()->changeStatus() !== true) {
            throw new Exception('The status for this page cannot be changed');
        }

        return true;
    }

    public static function changeStatusToListed(Page $page, int $position)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new Exception('The status for this page cannot be changed');
        }

        if ($position !== null && $position < 0) {
            throw new Exception('Invalid position');
        }

        if ($page->isDraft() === true && empty($page->errors()) === false) {
            throw new PermissionException([
                'key'     => 'page.changeStatus.incomplete',
                'details' => $page->errors()
            ]);

            throw new Exception('The page has errors and cannot be published');
        }

        return true;
    }

    public static function changeStatusToUnlisted(Page $page)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new Exception('The status for this page cannot be changed');
        }

        return true;
    }

    public static function changeTemplate(Page $page, string $template): bool
    {
        if ($page->permissions()->changeTemplate() !== true) {
            throw new Exception('The template for this page cannot be changed');
        }

        return true;
    }

    public static function changeTitle(Page $page, string $title): bool
    {
        if ($page->permissions()->changeTitle() !== true) {
            throw new Exception('The title for this page cannot be changed');
        }

        return true;
    }

    public static function create(Page $page): bool
    {
        if ($page->permissions()->create() !== true) {
            throw new Exception('This page cannot be created');
        }

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
        if ($page->permissions()->delete() !== true) {
            throw new Exception('This page cannot be deleted');
        }

        if ($page->exists() === false) {
            throw new Exception('The page does not exist');
        }

        if ($page->hasChildren() === true && $force === false) {
            throw new Exception('The page has children');
        }

        return true;
    }

    public static function update(Page $page, array $content = []): bool
    {
        if ($page->permissions()->update() !== true) {
            throw new Exception('This page cannot be updated');
        }

        return true;
    }

}
