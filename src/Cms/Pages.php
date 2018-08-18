<?php

namespace Kirby\Cms;

/**
 * The Pages collection contains
 * any number and mixture of page objects
 * They don't necessarily have to belong
 * to the same parent unless it is passed
 * as second argument in the constructor.
 *
 * Pages collection can be constructed very
 * easily:
 *
 * ```php
 * $collection = new Pages([
 *   new Page(['id' => 'project-a']),
 *   new Page(['id' => 'project-b']),
 *   new Page(['id' => 'project-c']),
 * ]);
 * ```
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Pages extends Collection
{

    /**
     * Cache for the index
     *
     * @var null|Pages
     */
    protected $index = null;

    /**
     * All registered pages methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * Returns all audio files of all children
     *
     * @return Files
     */
    public function audio(): Files
    {
        return $this->files()->filterBy("type", "audio");
    }

    /**
     * Returns all children for each page in the array
     *
     * @return Pages
     */
    public function children(): Pages
    {
        $children = new Pages([], $this->parent);

        foreach ($this->data as $pageKey => $page) {
            foreach ($page->children() as $childKey => $child) {
                $children->data[$childKey] = $child;
            }
        }

        return $children;
    }

    /**
     * Returns all code files of all children
     *
     * @return Files
     */
    public function code(): Files
    {
        return $this->files()->filterBy("type", "code");
    }

    /**
     * Returns all documents of all children
     *
     * @return Files
     */
    public function documents(): Files
    {
        return $this->files()->filterBy("type", "document");
    }

    /**
     * Fetch all drafts for all pages in the collection
     *
     * @return Pages
     */
    public function drafts()
    {
        $drafts = new Pages([], $this->parent);

        foreach ($this->data as $pageKey => $page) {
            foreach ($page->drafts() as $draftKey => $draft) {
                $drafts->data[$draftKey] = $draft;
            }
        }

        return $drafts;
    }

    /**
     * Creates a pages collection from an array of props
     *
     * @param array $pages
     * @param Model $parent
     * @param array $inject
     * @param bool $draft
     * @return Pages
     */
    public static function factory(array $pages, Model $model = null, bool $draft = false)
    {
        $model    = $model ?? App::instance()->site();
        $children = new static([], $model);
        $kirby    = $model->kirby();

        if (is_a($model, 'Kirby\Cms\Page') === true) {
            $parent = $model;
            $site   = $model->site();
        } else {
            $parent = null;
            $site   = $model;
        }

        foreach ($pages as $props) {
            $props['collection'] = $children;
            $props['kirby']      = $kirby;
            $props['parent']     = $parent;
            $props['site']       = $site;
            $props['isDraft']    = $draft;

            $page = Page::factory($props);

            $children->data[$page->id()] = $page;
        }

        return $children;
    }

    /**
     * Returns all files of all children
     *
     * @return Files
     */
    public function files(): Files
    {
        $files = new Files([], $this->parent);

        foreach ($this->data as $pageKey => $page) {
            foreach ($page->files() as $fileKey => $file) {
                $files->data[$fileKey] = $file;
            }
        }

        return $files;
    }

    /**
     * Finds a page in the collection by id.
     * This works recursively for children and
     * children of children, etc.
     *
     * @param string $id
     * @return mixed
     */
    public function findById($id)
    {
        $page      = $this->get($id);
        $multiLang = App::instance()->multilang();

        if ($multiLang === true) {
            $page = $this->findBy('slug', $id);
        }

        if (!$page) {
            $start = is_a($this->parent, 'Kirby\Cms\Page') === true ? $this->parent->id() : '';
            $page  = $this->findByIdRecursive($id, $start, $multiLang);
        }

        return $page;
    }

    /**
     * Finds a child or child of a child recursively.
     *
     * @param string $id
     * @param string $startAt
     * @return mixed
     */
    public function findByIdRecursive($id, $startAt = null, bool $multiLang = false)
    {
        $path       = explode('/', $id);
        $collection = $this;
        $item       = null;
        $query      = $startAt;

        foreach ($path as $key) {
            $query = ltrim($query . '/' . $key, '/');
            $item  = $collection->get($query) ?? null;

            if ($item === null && $multiLang === true) {
                $item = $collection->findBy('slug', $key);
            }

            if ($item === null) {
                return null;
            }

            $collection = $item->children();
        }

        return $item;
    }

    /**
     * Uses the specialized find by id method
     *
     * @param string $key
     * @return mixed
     */
    public function findByKey($key)
    {
        return $this->findById($key);
    }

    /**
     * Alias for Pages::findById
     *
     * @param string $id
     * @return Page|null
     */
    public function findByUri(string $id)
    {
        return $this->findById($id);
    }

    /**
     * Custom getter that is able to find
     * extension pages
     *
     * @param string $key
     * @return Page|null
     */
    public function get($key, $default = null)
    {
        if ($key === null) {
            return null;
        }

        if ($item = parent::get($key)) {
            return $item;
        }

        return App::instance()->extension('pages', $key);
    }

    /**
     * Returns all images of all children
     *
     * @return Files
     */
    public function images(): Files
    {
        return $this->files()->filterBy("type", "image");
    }

    /**
     * Create a recursive flat index of all
     * pages and subpages, etc.
     *
     * @return Pages
     */
    public function index(): Pages
    {
        if (is_a($this->index, 'Kirby\Cms\Pages') === true) {
            return $this->index;
        }

        $this->index = new Pages([], $this->parent);

        foreach ($this->data as $pageKey => $page) {
            $this->index->data[$pageKey] = $page;

            foreach ($page->index() as $childKey => $child) {
                $this->index->data[$childKey] = $child;
            }
        }

        return $this->index;
    }

    /**
     * Deprecated alias for Pages::unlisted()
     *
     * @return self
     */
    public function invisible(): self
    {
        return $this->filterBy('isUnlisted', '==', true);
    }

    /**
     * Returns all listed pages in the collection
     *
     * @return self
     */
    public function listed(): self
    {
        return $this->filterBy('isListed', '==', true);
    }

    /**
     * Returns all unlisted pages in the collection
     *
     * @return self
     */
    public function unlisted(): self
    {
        return $this->filterBy('isUnlisted', '==', true);
    }

    /**
     * Include all given items in the collection
     *
     * @return self
     */
    public function merge(...$args): self
    {
        // merge multiple arguments at once
        if (count($args) > 1) {
            $collection = clone $this;
            foreach ($args as $arg) {
                $collection = $collection->merge($arg);
            }
            return $collection;
        }

        // merge all parent drafts
        if ($args[0] === 'drafts') {
            if ($parent = $this->parent()) {
                return $this->merge($parent->drafts());
            }

            return $this;
        }

        // merge an entire collection
        if (is_a($args[0], static::class) === true) {
            $collection = clone $this;
            $collection->data = array_merge($collection->data, $args[0]->data);
            return $collection;
        }

        // append a single page
        if (is_a($args[0], 'Kirby\Cms\Page') === true) {
            $collection = clone $this;
            return $collection->set($args[0]->id(), $args[0]);
        }

        // merge an array
        if (is_array($args[0]) === true) {
            $collection = clone $this;
            foreach ($args[0] as $arg) {
                $collection = $collection->merge($arg);
            }
            return $collection;
        }

        if (is_string($args[0]) === true) {
            return $this->merge(App::instance()->site()->find($args[0]));
        }

        return $this;
    }

    /*
     * Returns all listed and unlisted pages in the collection
     *
     * @return self
     */
    public function published(): self
    {
        return $this->filterBy('isDraft', '==', false);
    }

    /**
     * Filter all pages by the given template
     *
     * @param null|string|array $template
     * @return self
     */
    public function template($templates): self
    {
        if (empty($templates) === true) {
            return $this;
        }

        if (is_array($templates) === false) {
            $templates = [$templates];
        }

        return $this->filter(function ($page) use ($templates) {
            return in_array($page->intendedTemplate()->name(), $templates);
        });
    }

    /**
     * Returns all video files of all children
     *
     * @return Files
     */
    public function videos(): Files
    {
        return $this->files()->filterBy("type", "video");
    }

    /**
     * Deprecated alias for Pages::listed()
     *
     * @return self
     */
    public function visible(): self
    {
        return $this->filterBy('isListed', '==', true);
    }
}
