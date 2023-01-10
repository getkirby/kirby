<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Uuid\HasUuids;

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
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Pages extends Collection
{
	use HasUuids;

	/**
	 * Cache for the index only listed and unlisted pages
	 *
	 * @var \Kirby\Cms\Pages|null
	 */
	protected $index = null;

	/**
	 * Cache for the index all statuses also including drafts
	 *
	 * @var \Kirby\Cms\Pages|null
	 */
	protected $indexWithDrafts = null;

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
	 * @param \Kirby\Cms\Pages|\Kirby\Cms\Page|string $object
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException When no `Page` or `Pages` object or an ID of an existing page is passed
	 */
	public function add($object)
	{
		$site = App::instance()->site();

		// add a pages collection
		if ($object instanceof self) {
			$this->data = array_merge($this->data, $object->data);

		// add a page by id
		} elseif (
			is_string($object) === true &&
			$page = $site->find($object)
		) {
			$this->__set($page->id(), $page);

		// add a page object
		} elseif ($object instanceof Page) {
			$this->__set($object->id(), $object);

		// give a useful error message on invalid input;
		// silently ignore "empty" values for compatibility with existing setups
		} elseif (in_array($object, [null, false, true], true) !== true) {
			throw new InvalidArgumentException('You must pass a Pages or Page object or an ID of an existing page to the Pages collection');
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
		$children = new Pages([]);

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
		$drafts = new Pages([]);

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
	 * @return static
	 */
	public static function factory(array $pages, Model $model = null, bool $draft = false)
	{
		$model  ??= App::instance()->site();
		$children = new static([], $model);
		$kirby    = $model->kirby();

		if ($model instanceof Page) {
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
	 * Finds a page by its ID or URI
	 * @internal Use `$pages->find()` instead
	 *
	 * @param string|null $key
	 * @return \Kirby\Cms\Page|null
	 */
	public function findByKey(string|null $key = null)
	{
		if ($key === null) {
			return null;
		}

		if ($page = $this->findByUuid($key, 'page')) {
			return $page;
		}

		// remove trailing or leading slashes
		$key = trim($key, '/');

		// strip extensions from the id
		if (strpos($key, '.') !== false) {
			$info = pathinfo($key);

			if ($info['dirname'] !== '.') {
				$key = $info['dirname'] . '/' . $info['filename'];
			} else {
				$key = $info['filename'];
			}
		}

		// try the obvious way
		if ($page = $this->get($key)) {
			return $page;
		}

		// try to find the page by its (translated) URI by stepping through the page tree
		$start = $this->parent instanceof Page ? $this->parent->id() : '';
		if ($page = $this->findByKeyRecursive($key, $start, App::instance()->multilang())) {
			return $page;
		}

		// for secondary languages, try the full translated URI
		// (for collections without parent that won't have a result above)
		if (
			App::instance()->multilang() === true &&
			App::instance()->language()->isDefault() === false &&
			$page = $this->findBy('uri', $key)
		) {
			return $page;
		}

		return null;
	}

	/**
	 * Finds a child or child of a child recursively
	 *
	 * @return mixed
	 */
	protected function findByKeyRecursive(string $id, string $startAt = null, bool $multiLang = false)
	{
		$path       = explode('/', $id);
		$item       = null;
		$query      = $startAt;

		foreach ($path as $key) {
			$collection = $item ? $item->children() : $this;
			$query = ltrim($query . '/' . $key, '/');
			$item  = $collection->get($query) ?? null;

			if ($item === null && $multiLang === true && !App::instance()->language()->isDefault()) {
				if (count($path) > 1 || $collection->parent()) {
					// either the desired path is definitely not a slug, or collection is the children of another collection
					$item = $collection->findBy('slug', $key);
				} else {
					// desired path _could_ be a slug or a "top level" uri
					$item = $collection->findBy('uri', $key);
				}
			}

			if ($item === null) {
				return null;
			}
		}

		return $item;
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
		// get object property by cache mode
		$index = $drafts === true ? $this->indexWithDrafts : $this->index;

		if ($index instanceof self) {
			return $index;
		}

		$index = new Pages([]);

		foreach ($this->data as $pageKey => $page) {
			$index->data[$pageKey] = $page;
			$pageIndex = $page->index($drafts);

			if ($pageIndex) {
				foreach ($pageIndex as $childKey => $child) {
					$index->data[$childKey] = $child;
				}
			}
		}

		if ($drafts === true) {
			return $this->indexWithDrafts = $index;
		}

		return $this->index = $index;
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
	 * @return $this|static
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
		if ($args[0] instanceof self) {
			$collection = clone $this;
			$collection->data = array_merge($collection->data, $args[0]->data);
			return $collection;
		}

		// append a single page
		if ($args[0] instanceof Page) {
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
