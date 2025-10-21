<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Uuid\HasUuids;
use Throwable;

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
 *
 * @template TPage of \Kirby\Cms\Page
 * @extends \Kirby\Cms\Collection<TPage>
 */
class Pages extends Collection
{
	use HasUuids;

	/**
	 * Cache for the index only listed and unlisted pages
	 */
	protected Pages|null $index = null;

	/**
	 * Cache for the index all statuses also including drafts
	 */
	protected Pages|null $indexWithDrafts = null;

	/**
	 * All registered pages methods
	 */
	public static array $methods = [];

	/**
	 * @var \Kirby\Cms\Page|\Kirby\Cms\Site|null
	 */
	protected object|null $parent = null;

	/**
	 * Adds a single page or
	 * an entire second collection to the
	 * current collection
	 *
	 * @param \Kirby\Cms\Pages<TPage>|TPage|string $object
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException When no `Page` or `Pages` object or an ID of an existing page is passed
	 */
	public function add($object): static
	{
		$site = App::instance()->site();

		// add a pages collection
		if ($object instanceof self) {
			$this->data = [...$this->data, ...$object->data];

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
			throw new InvalidArgumentException(
				message: 'You must pass a Pages or Page object or an ID of an existing page to the Pages collection'
			);
		}

		return $this;
	}

	/**
	 * Returns all audio files of all children
	 */
	public function audio(): Files
	{
		return $this->files()->filter('type', 'audio');
	}

	/**
	 * Returns all children for each page in the array
	 * @return \Kirby\Cms\Pages<TPage>
	 */
	public function children(): static
	{
		$children = new static([]);

		foreach ($this->data as $page) {
			foreach ($page->children() as $childKey => $child) {
				$children->data[$childKey] = $child;
			}
		}

		return $children;
	}

	/**
	 * Returns all code files of all children
	 */
	public function code(): Files
	{
		return $this->files()->filter('type', 'code');
	}

	/**
	 * Deletes the pages with the given IDs
	 * if they exist in the collection
	 *
	 * @throws \Kirby\Exception\Exception If not all pages could be deleted
	 */
	public function delete(array $ids): void
	{
		$exceptions = [];
		$kirby      = App::instance();

		// delete all pages and collect errors
		foreach ($ids as $id) {
			try {
				// Explanation: We get the page object from the global context
				// as the objects in the pages collection itself could have rendered
				// outdated from a sibling delete action in this loop (e.g. resorting
				// after deleting a sibling page and leaving the object in this collection
				// with an old root path).
				//
				// TODO: We can remove this part as soon
				// as we move away from our immutable object architecture.
				$page = $kirby->page($id);

				if ($page === null || $this->get($id) instanceof Page === false) {
					throw new NotFoundException(
						key: 'page.undefined',
					);
				}

				$page->delete();
				$this->remove($id);
			} catch (Throwable $e) {
				$exceptions[$id] = $e;
			}
		}

		if ($exceptions !== []) {
			throw new Exception(
				key: 'page.delete.multiple',
				details: $exceptions
			);
		}
	}

	/**
	 * Returns all documents of all children
	 */
	public function documents(): Files
	{
		return $this->files()->filter('type', 'document');
	}

	/**
	 * Fetch all drafts for all pages in the collection
	 * @return \Kirby\Cms\Pages<TPage>
	 */
	public function drafts(): static
	{
		$drafts = new static([]);

		foreach ($this->data as $page) {
			foreach ($page->drafts() as $draftKey => $draft) {
				$drafts->data[$draftKey] = $draft;
			}
		}

		return $drafts;
	}

	/**
	 * Creates a pages collection from an array of props
	 */
	public static function factory(
		array $pages,
		Page|Site|null $model = null,
		bool|null $draft = null
	): static {
		$model  ??= App::instance()->site();
		$children = new static([], $model);

		if ($model instanceof Page) {
			$parent = $model;
			$site   = $model->site();
		} else {
			$parent = null;
			$site   = $model;
		}

		foreach ($pages as $props) {
			$props['parent']  = $parent;
			$props['site']    = $site;
			$props['isDraft'] = $draft ?? $props['isDraft'] ?? $props['draft'] ?? false;

			$page = Page::factory($props);

			$children->data[$page->id()] = $page;
		}

		return $children;
	}

	/**
	 * Returns all files of all children
	 */
	public function files(): Files
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
	 * @return TPage|null
	 */
	public function findByKey(string|null $key = null): Page|null
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
		if (str_contains($key, '.') === true) {
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

		$kirby     = App::instance();
		$multiLang = $kirby->multilang();

		// try to find the page by its (translated) URI
		// by stepping through the page tree
		$start = $this->parent instanceof Page ? $this->parent->id() : '';
		if ($page = $this->findByKeyRecursive($key, $start, $multiLang)) {
			return $page;
		}

		// for secondary languages, try the full translated URI
		// (for collections without parent that won't have a result above)
		if (
			$multiLang === true &&
			$kirby->language()->isDefault() === false &&
			$page = $this->findBy('uri', $key)
		) {
			return $page;
		}

		return null;
	}

	/**
	 * Finds a child or child of a child recursively
	 * @return TPage|null
	 */
	protected function findByKeyRecursive(
		string $id,
		string|null $startAt = null,
		bool $multiLang = false
	): Page|null {
		$path       = explode('/', $id);
		$item       = null;
		$query      = $startAt;

		foreach ($path as $key) {
			$collection = $item?->children() ?? $this;
			$query      = ltrim($query . '/' . $key, '/');
			$item       = $collection->get($query) ?? null;

			if (
				$item === null &&
				$multiLang === true &&
				App::instance()->language()->isDefault() === false
			) {
				if (count($path) > 1 || $collection->parent()) {
					// either the desired path is definitely not a slug,
					// or collection is the children of another collection
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
	 * @return TPage|null
	 */
	public function findOpen(): Page|null
	{
		return $this->findBy('isOpen', true);
	}

	/**
	 * Custom getter that is able to find
	 * extension pages
	 * @return TPage|null
	 */
	public function get(string $key, mixed $default = null): Page|null
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
	 */
	public function images(): Files
	{
		return $this->files()->filter('type', 'image');
	}

	/**
	 * Create a recursive flat index of all
	 * pages and subpages, etc.
	 */
	public function index(bool $drafts = false): static
	{
		// get object property by cache mode
		$index = $drafts === true ? $this->indexWithDrafts : $this->index;

		if ($index instanceof Pages) {
			return $index;
		}

		$index = new static([]);

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
	 * @return \Kirby\Cms\Pages<TPage>
	 */
	public function listed(): static
	{
		return $this->filter('isListed', '==', true);
	}

	/**
	 * Returns all unlisted pages in the collection
	 * @return \Kirby\Cms\Pages<TPage>
	 */
	public function unlisted(): static
	{
		return $this->filter('isUnlisted', '==', true);
	}

	/**
	 * Include all given items in the collection
	 *
	 * @return $this|static
	 */
	public function merge(string|Pages|Page|array ...$args): static
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
		if ($args[0] instanceof Pages) {
			$collection       = clone $this;
			$collection->data = [...$collection->data, ...$args[0]->data];
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
	 * @return $this|static
	 */
	public function notTemplate(string|array|null $templates): static
	{
		if (empty($templates) === true) {
			return $this;
		}

		if (is_array($templates) === false) {
			$templates = [$templates];
		}

		return $this->filter(
			fn ($page) => in_array($page->intendedTemplate()->name(), $templates, true) === false
		);
	}

	/**
	 * Returns an array with all page numbers
	 */
	public function nums(): array
	{
		return $this->pluck('num');
	}

	/**
	 * Returns all listed and unlisted pages in the collection
	 * @return \Kirby\Cms\Pages<TPage>
	 */
	public function published(): static
	{
		return $this->filter('isDraft', '==', false);
	}

	/**
	 * Filter all pages by the given template
	 *
	 * @return $this|static
	 */
	public function template(string|array|null $templates): static
	{
		if (empty($templates) === true) {
			return $this;
		}

		if (is_array($templates) === false) {
			$templates = [$templates];
		}

		return $this->filter(
			fn ($page) => in_array($page->intendedTemplate()->name(), $templates, true)
		);
	}

	/**
	 * Returns all video files of all children
	 */
	public function videos(): Files
	{
		return $this->files()->filter('type', 'video');
	}
}
