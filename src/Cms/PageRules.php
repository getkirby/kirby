<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;

/**
 * Validators for all page actions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class PageRules
{
    /**
     * Validates if the sorting number of the page can be changed
     *
     * @param \Kirby\Cms\Page $page
     * @param int|null $num
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException If the given number is invalid
     */
    public static function changeNum(Page $page, int $num = null): bool
    {
        if ($num !== null && $num < 0) {
            throw new InvalidArgumentException(['key' => 'page.num.invalid']);
        }

        return true;
    }

    /**
     * Validates if the slug for the page can be changed
     *
     * @param \Kirby\Cms\Page $page
     * @param string $slug
     * @return bool
     * @throws \Kirby\Exception\DuplicateException If a page with this slug already exists
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the slug
     */
    public static function changeSlug(Page $page, string $slug): bool
    {
        if ($page->permissions()->changeSlug() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeSlug.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        self::validateSlugLength($slug);

        $siblings = $page->parentModel()->children();
        $drafts   = $page->parentModel()->drafts();

        if ($duplicate = $siblings->find($slug)) {
            if ($duplicate->is($page) === false) {
                throw new DuplicateException([
                    'key'  => 'page.duplicate',
                    'data' => [
                        'slug' => $slug
                    ]
                ]);
            }
        }

        if ($duplicate = $drafts->find($slug)) {
            if ($duplicate->is($page) === false) {
                throw new DuplicateException([
                    'key'  => 'page.draft.duplicate',
                    'data' => [
                        'slug' => $slug
                    ]
                ]);
            }
        }

        return true;
    }

    /**
     * Validates if the status for the page can be changed
     *
     * @param \Kirby\Cms\Page $page
     * @param string $status
     * @param int|null $position
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException If the given status is invalid
     */
    public static function changeStatus(Page $page, string $status, int $position = null): bool
    {
        if (isset($page->blueprint()->status()[$status]) === false) {
            throw new InvalidArgumentException(['key' => 'page.status.invalid']);
        }

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

    /**
     * Validates if a page can be converted to a draft
     *
     * @param \Kirby\Cms\Page $page
     * @return bool
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the status or the page cannot be converted to a draft
     */
    public static function changeStatusToDraft(Page $page)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        if ($page->isHomeOrErrorPage() === true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.toDraft.invalid',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        return true;
    }

    /**
     * Validates if the status of a page can be changed to listed
     *
     * @param \Kirby\Cms\Page $page
     * @param int $position
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException If the given position is invalid
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the status or the status for the page cannot be changed by any user
     */
    public static function changeStatusToListed(Page $page, int $position)
    {
        // no need to check for status changing permissions,
        // instead we need to check for sorting permissions
        if ($page->isListed() === true) {
            if ($page->isSortable() !== true) {
                throw new PermissionException([
                    'key'  => 'page.sort.permission',
                    'data' => [
                        'slug' => $page->slug()
                    ]
                ]);
            }

            return true;
        }

        if ($page->permissions()->changeStatus() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
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

    /**
     * Validates if the status of a page can be changed to unlisted
     *
     * @param \Kirby\Cms\Page $page
     * @return bool
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the status
     */
    public static function changeStatusToUnlisted(Page $page)
    {
        if ($page->permissions()->changeStatus() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeStatus.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        return true;
    }

    /**
     * Validates if the template of the page can be changed
     *
     * @param \Kirby\Cms\Page $page
     * @param string $template
     * @return bool
     * @throws \Kirby\Exception\LogicException If the template of the page cannot be changed at all
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the template
     */
    public static function changeTemplate(Page $page, string $template): bool
    {
        if ($page->permissions()->changeTemplate() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeTemplate.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        if (count($page->blueprints()) <= 1) {
            throw new LogicException([
                'key'  => 'page.changeTemplate.invalid',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        return true;
    }

    /**
     * Validates if the title of the page can be changed
     *
     * @param \Kirby\Cms\Page $page
     * @param string $title
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException If the new title is empty
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the title
     */
    public static function changeTitle(Page $page, string $title): bool
    {
        if (Str::length($title) === 0) {
            throw new InvalidArgumentException([
                'key' => 'page.changeTitle.empty',
            ]);
        }

        if ($page->permissions()->changeTitle() !== true) {
            throw new PermissionException([
                'key'  => 'page.changeTitle.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        return true;
    }

    /**
     * Validates if the page can be created
     *
     * @param \Kirby\Cms\Page $page
     * @return bool
     * @throws \Kirby\Exception\DuplicateException If the same page or a draft already exists
     * @throws \Kirby\Exception\InvalidArgumentException If the slug is invalid
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to create this page
     */
    public static function create(Page $page): bool
    {
        if (Str::length($page->slug()) < 1) {
            throw new InvalidArgumentException([
                'key' => 'page.slug.invalid',
            ]);
        }

        self::validateSlugLength($page->slug());

        if ($page->exists() === true) {
            throw new DuplicateException([
                'key'  => 'page.draft.duplicate',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        if ($page->permissions()->create() !== true) {
            throw new PermissionException([
                'key' => 'page.create.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        $siblings = $page->parentModel()->children();
        $drafts   = $page->parentModel()->drafts();
        $slug     = $page->slug();

        if ($siblings->find($slug)) {
            throw new DuplicateException([
                'key'  => 'page.duplicate',
                'data' => ['slug' => $slug]
            ]);
        }

        if ($drafts->find($slug)) {
            throw new DuplicateException([
                'key'  => 'page.draft.duplicate',
                'data' => ['slug' => $slug]
            ]);
        }

        return true;
    }

    /**
     * Validates if the page can be deleted
     *
     * @param \Kirby\Cms\Page $page
     * @param bool $force
     * @return bool
     * @throws \Kirby\Exception\LogicException If the page has children and should not be force-deleted
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to delete the page
     */
    public static function delete(Page $page, bool $force = false): bool
    {
        if ($page->permissions()->delete() !== true) {
            throw new PermissionException([
                'key' => 'page.delete.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        if (($page->hasChildren() === true || $page->hasDrafts() === true) && $force === false) {
            throw new LogicException(['key' => 'page.delete.hasChildren']);
        }

        return true;
    }

    /**
     * Validates if the page can be duplicated
     *
     * @param \Kirby\Cms\Page $page
     * @param string $slug
     * @param array $options
     * @return bool
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to duplicate the page
     */
    public static function duplicate(Page $page, string $slug, array $options = []): bool
    {
        if ($page->permissions()->duplicate() !== true) {
            throw new PermissionException([
                'key' => 'page.duplicate.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        return true;
    }

    /**
     * Validates if the page can be updated
     *
     * @param \Kirby\Cms\Page $page
     * @param array $content
     * @return bool
     * @throws \Kirby\Exception\PermissionException If the user is not allowed to update the page
     */
    public static function update(Page $page, array $content = []): bool
    {
        if ($page->permissions()->update() !== true) {
            throw new PermissionException([
                'key'  => 'page.update.permission',
                'data' => [
                    'slug' => $page->slug()
                ]
            ]);
        }

        return true;
    }

    /**
     * Ensures that the slug doesn't exceed the maximum length to make
     * sure that the directory name will be accepted by the filesystem
     *
     * @param string $slug New slug to check
     * @return void
     * @throws \Kirby\Exception\InvalidArgumentException If the slug is too long
     */
    protected static function validateSlugLength(string $slug): void
    {
        if ($slugsMaxlength = App::instance()->option('slugs.maxlength', 255)) {
            $maxlength = (int)$slugsMaxlength;

            if (Str::length($slug) > $maxlength) {
                throw new InvalidArgumentException([
                    'key'  => 'page.slug.maxlength',
                    'data' => [
                        'length' => $maxlength
                    ]
                ]);
            }
        }
    }
}
