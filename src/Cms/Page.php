<?php

namespace Kirby\Cms;

use Exception;

/**
 * The Page class is the heart and soul of
 * Kirby. It is used to construct pages and
 * all their dependencies like children,
 * files, content, etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Page extends Object
{

    use HasChildren;
    use HasContent;
    use HasFiles;
    use HasSiblings;

    /**
     * Creates a new page object
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'children' => [
                'type'    => Children::class,
                'default' => function (): Children {
                    return $this->store()->commit('page.children', $this);
                }
            ],
            'collection' => [
                'type'    => Pages::class,
                'default' => function () {
                    if ($parent = $this->parent()) {
                        return $parent->children();
                    }
                    return $this->site()->children();
                }
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function (): Content {
                    return $this->store()->commit('page.content', $this);
                }
            ],
            'files' => [
                'type' => Files::class,
                'default' => function (): Files {
                    return $this->store()->commit('page.files', $this);
                }
            ],
            'id' => [
                'required' => true,
                'type'     => 'string',
            ],
            'num' => [
                'type' => 'integer'
            ],
            'parent' => [
                'type' => Page::class,
            ],
            'root' => [
                'type' => 'string',
            ],
            'site' => [
                'type'    => Site::class,
                'default' => function () {
                    return $this->plugin('site');
                }
            ],
            'store' => [
                'type'    => Store::class,
                'default' => function () {
                    return $this->plugin('store');
                }
            ],
            'template' => [
                'type'    => 'string',
                'default' => function () {
                    return $this->store()->commit('page.template', $this);
                }
            ],
            'url' => [
                'type'    => 'string',
                'default' => function () {
                    return '/' . ltrim($this->id(), '/');
                }
            ],
        ]);

    }

    /**
     * Changes the slug/uid of the page
     *
     * @param string $slug
     * @return self
     */
    public function changeSlug(string $slug): self
    {
        $this->rules()->check('page.change.slug', $this, $slug);
        $this->perms()->check('page.change.slug', $this, $slug);

        return $this->store()->commit('page.change.slug', $this, $slug);
    }

    /**
     * Changes the page template
     *
     * @param string $template
     * @return self
     */
    public function changeTemplate(string $template): self
    {
        $this->rules()->check('page.change.template', $this, $template);
        $this->perms()->check('page.change.template', $this, $template);

        return $this->store()->commit('page.change.template', $this, $template);
    }

    /**
     * Changes the visibility/status of the page
     *
     * @param string $status
     * @param int $position
     * @return self
     */
    public function changeStatus(string $status, int $position = null): self
    {
        $this->rules()->check('page.change.status', $this, $status, $position);
        $this->perms()->check('page.change.status', $this, $status, $position);

        return $this->store()->commit('page.change.status', $this, $status, $position);
    }

    /**
     * Clones the current page object with basic
     * initial values for the clone
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'id'     => $this->id(),
            'root'   => $this->root(),
            'url'    => $this->url(),
            'parent' => $this->parent()
        ], $props));
    }

    /**
     * Creates a new page
     *
     * @param Page $parent
     * @param string $slug
     * @param string $template
     * @param array $content
     * @return self
     */
    public static function create(Page $parent = null, string $slug, string $template, array $content = []): self
    {
        static::rules()->check('page.create', $parent, $slug, $template, $content);
        static::perms()->check('page.create', $parent, $slug, $template, $content);

        return static::store()->commit('page.create', $parent, $slug, $template, $content);
    }

    /**
     * Deletes the page
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->check('page.delete', $this);
        $this->perms()->check('page.delete', $this);

        return $this->store()->commit('page.delete', $this);
    }

    /**
     * Checks if the page exists in the store
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->store()->commit('page.exists', $this);
    }

    /**
     * Checks if there's a next invisible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasNextInvisible(): bool
    {
        return $this->nextInvisible() !== null;
    }

    /**
     * Checks if there's a next visible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasNextVisible(): bool
    {
        return $this->nextVisible() !== null;
    }

    /**
     * Checks if there's a previous invisible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasPrevInvisible(): bool
    {
        return $this->prevInvisible() !== null;
    }

    /**
     * Checks if there's a previous visible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasPrevVisible(): bool
    {
        return $this->prevVisible() !== null;
    }

    /**
     * Changes the status to unlisted
     *
     * @return self
     */
    public function hide(): self
    {
        return $this->changeStatus('unlisted');
    }

    /**
     * Checks if the page is the current page
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->site()->page()->is($this);
    }

    /**
     * Checks if the page is the error page
     *
     * @return bool
     */
    public function isErrorPage(): bool
    {
        return $this->site()->errorPage()->is($this);
    }

    /**
     * Checks if the page is the home page
     *
     * @return bool
     */
    public function isHomePage(): bool
    {
        return $this->site()->homePage()->is($this);
    }

    /**
     * Checks if the page is invisible
     *
     * @return bool
     */
    public function isInvisible(): bool
    {
        return $this->isVisible() === false;
    }

    /**
     * Checks if the page is open.
     * Open pages are either the current one
     * or descendants of the current one.
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->isActive() || $this->site()->page()->parents()->has($this->id());
    }

    /**
     * Checks if the page is visible
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->num() !== null;
    }

    /**
     * Returns the next invisible page if it exists
     *
     * @return self|null
     */
    public function nextInvisible()
    {
        return $this->nextAll()->invisible()->first();
    }

    /**
     * Returns the next visible page if it exists
     *
     * @return self|null
     */
    public function nextVisible()
    {
        return $this->nextAll()->visible()->first();
    }

    /**
     * Returns the parent page if it exists
     *
     * @return self|null
     */
    public function parent()
    {
        return $this->prop('parent');
    }

    /**
     * Returns a list of all parents and their parents recursively
     *
     * @return Pages
     */
    public function parents(): Pages
    {
        $parents = new Pages;
        $page    = $this->parent();

        while ($page !== null) {
            $parents->append($page->id(), $page);
            $page = $page->parent();
        }

        return $parents;
    }

    /**
     * Returns the previous invisible page
     *
     * @return self|null
     */
    public function prevInvisible()
    {
        return $this->prevAll()->invisible()->first();
    }

    /**
     * Returns the previous visible page
     *
     * @return self|null
     */
    public function prevVisible()
    {
        return $this->prevAll()->visible()->first();
    }

    /**
     * Returns all sibling elements
     *
     * @return Children
     */
    public function siblings()
    {
        if ($parent = $this->parent()) {
            return $parent->children();
        }

        return $this->site()->children();
    }

    /**
     * Returns the slug of the page
     *
     * @return string
     */
    public function slug(): string
    {
        return basename($this->id());
    }

    /**
     * Changes the page number
     *
     * @param int $position
     * @return self
     */
    public function sort(int $position): self
    {
        return $this->changeStatus('listed', $position);
    }

    /**
     * Returns the title field or the slug as fallback
     *
     * @return Field
     */
    public function title(): Field
    {
        return $this->content()->get('title')->or($this->slug());
    }

    /**
     * Returns the UID of the page
     *
     * @see self::slug()
     * @return string
     */
    public function uid(): string
    {
        return $this->slug();
    }

    /**
     * Updates the page content
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        $this->rules()->check('page.update', $this, $content);
        $this->perms()->check('page.update', $this, $content);

        return $this->store()->commit('page.update', $this, $content);
    }

}
