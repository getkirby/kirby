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
     * Validation for changing page num
     *
     * @param \Kirby\Cms\Page $page
     * @param int|null $num
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function changeNum(Page $page, int $num = null): bool
    {
        if ($num !== null && $num < 0) {
            throw new InvalidArgumentException(['key' => 'page.num.invalid']);
        }

        return true;
    }

    /**
     * Validation for changing page slug
     *
     * @param \Kirby\Cms\Page $page
     * @param string $slug
     * @return bool
     * @throws \Kirby\Exception\DuplicateException
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for changing page status
     *
     * @param \Kirby\Cms\Page $page
     * @param string $status
     * @param int|null $position
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
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
     * Validation for changing page status to draft
     *
     * @param \Kirby\Cms\Page $page
     * @return bool
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for changing page status to listed
     *
     * @param \Kirby\Cms\Page $page
     * @param int $position
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for changing page status to unlisted
     *
     * @param \Kirby\Cms\Page $page
     * @return bool
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for changing page template
     *
     * @param \Kirby\Cms\Page $page
     * @param string $template
     * @return bool
     * @throws \Kirby\Exception\LogicException
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for changing page title
     *
     * @param \Kirby\Cms\Page $page
     * @param string $title
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for page create
     *
     * @param \Kirby\Cms\Page $page
     * @return bool
     * @throws \Kirby\Exception\DuplicateException
     * @throws \Kirby\Exception\InvalidArgumentException
     * @throws \Kirby\Exception\PermissionException
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

    /**
     * Validation for page delete
     *
     * @param \Kirby\Cms\Page $page
     * @param bool $force
     * @return bool
     * @throws \Kirby\Exception\LogicException
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for page duplicate
     *
     * @param \Kirby\Cms\Page $page
     * @param string $slug
     * @param array $options
     * @return bool
     * @throws \Kirby\Exception\PermissionException
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
     * Validation for page update
     *
     * @param \Kirby\Cms\Page $page
     * @param array $content
     * @return bool
     * @throws \Kirby\Exception\PermissionException
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
