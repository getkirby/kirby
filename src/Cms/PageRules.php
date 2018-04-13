<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;

class PageRules
{

    public static function changeNum(Page $page, int $num = null): bool
    {
        if ($num !== null && $num < 0) {
            throw new InvalidArgumentException(['key' => 'page.num.invalid']);
        }

        return true;
    }

    public static function changeSlug(Page $page, string $slug): bool
    {
        if ($page->permissions()->changeSlug() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeSlug.permission',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        if ($duplicate = $page->siblings()->not($page)->find($slug)) {
            throw new DuplicateException([
                'key'  => 'page.duplicate',
                'data' => ['slug' => $slug]
            ]);
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
                throw new InvalidArgumentException(['key' => 'page.status.invalid']);
        }
    }

    public static function changeStatusToDraft(Page $page)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.permission',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        if ($page->isHomeOrErrorPage() === true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.toDraft.invalid',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        return true;
    }

    public static function changeStatusToListed(Page $page, int $position)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.permission',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        if ($position !== null && $position < 0) {
            throw new InvalidArgumentException(['key' => 'page.num.invalid']);
        }

        if ($page->isDraft() === true && empty($page->errors()) === false) {
            throw new PermissionException([
                'key'     => 'page.changeStatus.incomplete',
                'details' => $page->errors()
            ]);
        }

        return true;
    }

    public static function changeStatusToUnlisted(Page $page)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.permission',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        return true;
    }

    public static function changeTemplate(Page $page, string $template): bool
    {
        if ($page->permissions()->changeTemplate() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeTemplate.permission',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        return true;
    }

    public static function changeTitle(Page $page, string $title): bool
    {
        if ($page->permissions()->changeTitle() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeTitle.permission',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        return true;
    }

    public static function create(Page $page): bool
    {
        if ($page->permissions()->create() !== true) {
            throw new PermissionException(['key' => 'page.create.permission']);
        }

        $siblings = $page->parentModel()->children();
        $drafts   = $page->parentModel()->drafts();
        $slug     = $page->slug();

        if ($duplicate = $siblings->find($slug)) {
            throw new DuplicateException([
                'key'  => 'page.duplicate',
                'data' => ['slug' => $slug]
            ]);
        }

        if ($duplicate = $drafts->find($slug)) {
            throw new DuplicateException([
                'key'  => 'page.draft.duplicate',
                'data' => ['slug' => $slug]
            ]);
        }

        return true;
    }

    public static function delete(Page $page, bool $force = false): bool
    {
        if ($page->permissions()->delete() !== true) {
            throw new PermissionException(['key' => 'page.delete.permission']);
        }

        if ($page->exists() === false) {
            throw new NotFoundException(['key' => 'page.undefined']);
        }

        if ($page->hasChildren() === true && $force === false) {
            throw new LogicException(['key' => 'page.delete.hasChildren']);
        }

        return true;
    }

    public static function update(Page $page, array $content = []): bool
    {
        if ($page->permissions()->update() !== true) {
            throw new PermissionException(['key' => 'page.update.permission']);
        }

        return true;
    }

}
