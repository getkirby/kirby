<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The `$pages` object refers to a
 * collection of pages. The pages in this
 * collection can have the same or different
 * parents, they can actually exist as
 * subfolders in the content folder or be
 * virtual pages created from a database,
 * an Excel sheet, any API or any other
 * source.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Pages extends Collection
{
    /**
     * Cache for the index
     *
     * @var \Kirby\Cms\Pages|null
     */
    protected $index = null;

    /**
     * All registered pages methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * Adds a single page or
     * an entire second collection to the
     * current collection
     *
     * @param mixed $object
     * @return self
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function add($object)
    {
        // add a page collection
        if (is_a($object, static::class) === true) {
            $this->data = array_merge($this->data, $object->data);

        // add a page by id
        } elseif (is_string($object) === true && $page = page($object)) {
            $this->__set($page->id(), $page);

        // add a page object
        } elseif (is_a($object, 'Kirby\Cms\Page') === true) {
            $this->__set($object->id(), $object);

        // give a useful error message on invalid input
        } elseif (in_array($object, [null, false, true], true) !== true) {
            throw new InvalidArgumentException('You must pass a Page object to the Pages collection');
        }

        return $this;
    }

    /**
     * Returns all audio files of all children
     *
     * @return \Kirby\Cms\Files
     */
    public function audio()
    {
        return $this->files()->filter('type', 'audio');
    }

    /**
     * Returns all children for each page in the array
     *
     * @return \Kirby\Cms\Pages
     */
    public function children()
    {
        $children = new Pages([], $this->parent);

        foreach ($this->data as $page) {
            foreach ($page->children() as $childKey => $child) {
                $children->data[$childKey] = $child;
            }
        }

        return $children;
    }

    /**
     * Returns all code files of all children
     *
     * @return \Kirby\Cms\Files
     */
    public function code()
    {
        return $this->files()->filter('type', 'code');
    }

    /**
     * Returns all documents of all children
     *
     * @return \Kirby\Cms\Files
     */
    public function documents()
    {
        return $this->files()->filter('type', 'document');
    }

    /**
     * Fetch all drafts for all pages in the collection
     *
     * @return \Kirby\Cms\Pages
     */
    public function drafts()
    {
        $drafts = new Pages([], $this->parent);

        foreach ($this->data as $page) {
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
     * @param \Kirby\Cms\Model|null $model
     * @param bool $draft
     * @return self
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
            $props['kirby']   = $kirby;
            $props['parent']  = $parent;
            $props['site']    = $site;
            $props['isDraft'] = $draft;

            $page = Page::factory($props);

            $children->data[$page->id()] = $page;
        }

        return $children;
    }

    /**
     * Returns all files of all children
     *
     * @return \Kirby\Cms\Files
     */
    public function files()
    {
        $files = new Files([], $this->parent);

        foreach ($this->data as $page) {
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
     * @param string|null $id
     * @return mixed
     */
    public function findById(string $id = null)
    {
        // remove trailing or leading slashes
        $id = trim($id, '/');

        // strip extensions from the id
        if (strpos($id, '.') !== false) {
            $info = pathinfo($id);

            if ($info['dirname'] !== '.') {
                $id = $info['dirname'] . '/' . $info['filename'];
            } else {
                $id = $info['filename'];
            }
        }

        // try the obvious way
        if ($page = $this->get($id)) {
            return $page;
        }

        $multiLang = App::instance()->multilang();

        if ($multiLang === true && $page = $this->findBy('slug', $id)) {
            return $page;
        }

        $start = is_a($this->parent, 'Kirby\Cms\Page') === true ? $this->parent->id() : '';
        $page  = $this->findByIdRecursive($id, $start, $multiLang);

        return $page;
    }

    /**
     * Finds a child or child of a child recursively.
     *
     * @param string $id
     * @param string|null $startAt
     * @param bool $multiLang
     * @return mixed
     */
    public function findByIdRecursive(string $id, string $startAt = null, bool $multiLang = false)
    {
        $path       = explode('/', $id);
        $item       = null;
        $query      = $startAt;

        foreach ($path as $key) {
            $collection = $item ? $item->children() : $this;
            $query = ltrim($query . '/' . $key, '/');
            $item  = $collection->get($query) ?? null;

            if ($item === null && $multiLang === true) {
                $item = $collection->findBy('slug', $key);
            }

            if ($item === null) {
                return null;
            }
        }

        return $item;
    }

    /**
     * Uses the specialized find by id method
     *
     * @param string|null $key
     * @return mixed
     */
    public function findByKey(string $key = null)
    {
        return $this->findById($key);
    }

    /**
     * Alias for Pages::findById
     *
     * @param string $id
     * @return \Kirby\Cms\Page|null
     */
    public function findByUri(string $id)
    {
        return $this->findById($id);
    }

    /**
     * Finds the currently open page
     *
     * @return \Kirby\Cms\Page|null
     */
    public function findOpen()
    {
        return $this->findBy('isOpen', true);
    }

    /**
     * Custom getter that is able to find
     * extension pages
     *
     * @param string $key
     * @param mixed $default
     * @return \Kirby\Cms\Page|null
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
     * @return \Kirby\Cms\Files
     */
    public function images()
    {
        return $this->files()->filter('type', 'image');
    }

    /**
     * Create a recursive flat index of all
     * pages and subpages, etc.
     *
     * @param bool $drafts
     * @return \Kirby\Cms\Pages
     */
    public function index(bool $drafts = false)
    {
        if (is_a($this->index, 'Kirby\Cms\Pages') === true) {
            return $this->index;
        }

        $this->index = new Pages([], $this->parent);

        foreach ($this->data as $pageKey => $page) {
            $this->index->data[$pageKey] = $page;
            $index = $page->index($drafts);

            if ($index) {
                foreach ($index as $childKey => $child) {
                    $this->index->data[$childKey] = $child;
                }
            }
        }

        return $this->index;
    }

    /**
     * Returns all listed pages in the collection
     *
     * @return \Kirby\Cms\Pages
     */
    public function listed()
    {
        return $this->filter('isListed', '==', true);
    }

    /**
     * Returns all unlisted pages in the collection
     *
     * @return \Kirby\Cms\Pages
     */
    public function unlisted()
    {
        return $this->filter('isUnlisted', '==', true);
    }

    /**
     * Include all given items in the collection
     *
     * @param mixed ...$args
     * @return self
     */
    public function merge(...$args)
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

    /**
     * Filter all pages by excluding the given template
     * @since 3.3.0
     *
     * @param string|array $templates
     * @return \Kirby\Cms\Pages
     */
    public function notTemplate($templates)
    {
        if (empty($templates) === true) {
            return $this;
        }

        if (is_array($templates) === false) {
            $templates = [$templates];
        }

        return $this->filter(function ($page) use ($templates) {
            return !in_array($page->intendedTemplate()->name(), $templates);
        });
    }

    /**
     * Returns an array with all page numbers
     *
     * @return array
     */
    public function nums(): array
    {
        return $this->pluck('num');
    }

    /*
     * Returns all listed and unlisted pages in the collection
     *
     * @return \Kirby\Cms\Pages
     */
    public function published()
    {
        return $this->filter('isDraft', '==', false);
    }

    /**
     * Filter all pages by the given template
     *
     * @param string|array $templates
     * @return \Kirby\Cms\Pages
     */
    public function template($templates)
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
     * @return \Kirby\Cms\Files
     */
    public function videos()
    {
        return $this->files()->filter('type', 'video');
    }
}
