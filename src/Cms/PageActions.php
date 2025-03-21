<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\MemoryStorage;
use Kirby\Content\VersionCache;
use Kirby\Content\VersionId;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

/**
 * PageActions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait PageActions
{
	/**
	 * Changes the sorting number.
	 * The sorting number must already be correct
	 * when the method is called.
	 * This only affects this page,
	 * siblings will not be resorted.
	 *
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If a draft is being sorted or the directory cannot be moved
	 */
	public function changeNum(int|null $num = null): static
	{
		if ($this->isDraft() === true) {
			throw new LogicException(
				message: 'Drafts cannot change their sorting number'
			);
		}

		// don't run the action if everything stayed the same
		if ($this->num() === $num) {
			return $this;
		}

		return $this->commit('changeNum', ['page' => $this, 'num' => $num], function ($oldPage, $num) {
			$newPage = $oldPage->clone([
				'num'      => $num,
				'dirname'  => null,
				'root'     => null,
				'template' => $oldPage->intendedTemplate()->name(),
			]);

			// actually move the page on disk
			if ($oldPage->exists() === true) {
				if (Dir::move($oldPage->root(), $newPage->root()) === true) {
					// Updates the root path of the old page with the root path
					// of the moved new page to use fly actions on old page in loop
					$oldPage->root = $newPage->root();
				} else {
					throw new LogicException(
						message: 'The page directory cannot be moved'
					);
				}
			}

			return $newPage;
		});
	}

	/**
	 * Changes the slug/uid of the page
	 *
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the directory cannot be moved
	 */
	public function changeSlug(
		string $slug,
		string|null $languageCode = null
	): static {
		// always sanitize the slug
		$slug = Url::slug($slug);

		// in multi-language installations the slug for the non-default
		// languages is stored in the text file. The changeSlugForLanguage
		// method takes care of that.
		if ($this->kirby()->language($languageCode)?->isDefault() === false) {
			return $this->changeSlugForLanguage($slug, $languageCode);
		}

		// if the slug stays exactly the same,
		// nothing needs to be done.
		if ($slug === $this->slug()) {
			return $this;
		}

		$arguments = ['page' => $this, 'slug' => $slug, 'languageCode' => null];
		return $this->commit('changeSlug', $arguments, function ($oldPage, $slug) {
			$newPage = $oldPage->clone([
				'slug'     => $slug,
				'dirname'  => null,
				'root'     => null,
				'template' => $oldPage->intendedTemplate()->name(),
			]);

			// clear UUID cache recursively (for children and files as well)
			$oldPage->uuid()?->clear(true);

			if ($oldPage->exists() === true) {
				// actually move stuff on disk
				if (Dir::move($oldPage->root(), $newPage->root()) !== true) {
					throw new LogicException(
						message: 'The page directory cannot be moved'
					);
				}

				// hard reset for the version cache
				// to avoid broken/overlapping page references
				VersionCache::$cache = [];

				// remove from the siblings
				ModelState::update(
					method: 'remove',
					current: $oldPage,
				);

				Dir::remove($oldPage->mediaRoot());
			}

			return $newPage;
		});
	}

	/**
	 * Change the slug for a specific language
	 *
	 * @throws \Kirby\Exception\NotFoundException If the language for the given language code cannot be found
	 * @throws \Kirby\Exception\InvalidArgumentException If the slug for the default language is being changed
	 */
	protected function changeSlugForLanguage(
		string $slug,
		string|null $languageCode = null
	): static {
		$language = $this->kirby()->language($languageCode);

		if (!$language) {
			throw new NotFoundException(
				message: 'The language: "' . $languageCode . '" does not exist'
			);
		}

		if ($language->isDefault() === true) {
			throw new InvalidArgumentException(
				message: 'Use the changeSlug method to change the slug for the default language'
			);
		}

		$arguments = ['page' => $this, 'slug' => $slug, 'languageCode' => $language->code()];
		return $this->commit('changeSlug', $arguments, function ($page, $slug, $languageCode) {
			// remove the slug if it's the same as the folder name
			if ($slug === $page->uid()) {
				$slug = null;
			}

			return $page->save(['slug' => $slug], $languageCode);
		});
	}

	/**
	 * Change the status of the current page
	 * to either draft, listed or unlisted.
	 * If changing to `listed`, you can pass a position for the
	 * page in the siblings collection. Siblings will be resorted.
	 *
	 * @param string $status "draft", "listed" or "unlisted"
	 * @param int|null $position Optional sorting number
	 * @throws \Kirby\Exception\InvalidArgumentException If an invalid status is being passed
	 */
	public function changeStatus(
		string $status,
		int|null $position = null
	): static {
		return match ($status) {
			'draft'    => $this->changeStatusToDraft(),
			'listed'   => $this->changeStatusToListed($position),
			'unlisted' => $this->changeStatusToUnlisted(),
			default    => throw new InvalidArgumentException(
				message: 'Invalid status: ' . $status
			)
		};
	}

	protected function changeStatusToDraft(): static
	{
		$arguments = ['page' => $this, 'status' => 'draft', 'position' => null];
		$page = $this->commit(
			'changeStatus',
			$arguments,
			fn ($page) => $page->unpublish()
		);

		return $page;
	}

	/**
	 * @return $this|static
	 */
	protected function changeStatusToListed(int|null $position = null): static
	{
		// create a sorting number for the page
		$num = $this->createNum($position);

		// don't sort if not necessary
		if ($this->status() === 'listed' && $num === $this->num()) {
			return $this;
		}

		$page = $this->commit(
			'changeStatus',
			[
				'page'     => $this,
				'status'   => 'listed',
				'position' => $num
			],
			fn ($page, $status, $position) =>
				$page->publish()->changeNum($position)
		);

		if ($this->blueprint()->num() === 'default') {
			$page->resortSiblingsAfterListing($num);
		}

		return $page;
	}

	/**
	 * @return $this|static
	 */
	protected function changeStatusToUnlisted(): static
	{
		if ($this->status() === 'unlisted') {
			return $this;
		}

		$page = $this->commit(
			'changeStatus',
			[
				'page'     => $this,
				'status'   => 'unlisted',
				'position' => null
			],
			fn ($page) => $page->publish()->changeNum(null)
		);

		$this->resortSiblingsAfterUnlisting();

		return $page;
	}

	/**
	 * Change the position of the page in its siblings
	 * collection. Siblings will be resorted. If the page
	 * status isn't yet `listed`, it will be changed to it.
	 *
	 * @return $this|static
	 */
	public function changeSort(int|null $position = null): static
	{
		return $this->changeStatus('listed', $position);
	}

	/**
	 * Changes the page template
	 *
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the textfile cannot be renamed/moved
	 */
	public function changeTemplate(string $template): static
	{
		if ($template === $this->intendedTemplate()->name()) {
			return $this;
		}

		return $this->commit('changeTemplate', ['page' => $this, 'template' => $template], function ($oldPage, $template) {
			// convert for new template/blueprint
			return $oldPage->convertTo($template);
		});
	}

	/**
	 * Change the page title
	 */
	public function changeTitle(
		string $title,
		string|null $languageCode = null
	): static {
		// if the `$languageCode` argument is not set and is not the default language
		// the `$languageCode` argument is sent as the current language
		if (
			$languageCode === null &&
			$language = $this->kirby()->language()
		) {
			if ($language->isDefault() === false) {
				$languageCode = $language->code();
			}
		}

		$arguments = ['page' => $this, 'title' => $title, 'languageCode' => $languageCode];

		return $this->commit('changeTitle', $arguments, function ($page, $title, $languageCode) {
			return $page->save(['title' => $title], $languageCode);
		});
	}

	/**
	 * Commits a page action, by following these steps
	 *
	 * 1. applies the `before` hook
	 * 2. checks the action rules
	 * 3. commits the store action
	 * 4. applies the `after` hook
	 * 5. returns the result
	 */
	protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed {
		$commit = new ModelCommit(
			model: $this,
			action: $action
		);

		return $commit->call($arguments, $callback);
	}

	/**
	 * Copies the page to a new parent
	 * @internal
	 *
	 * @throws \Kirby\Exception\DuplicateException If the page already exists
	 */
	public function copy(array $options = []): static
	{
		$slug        = $options['slug']     ?? $this->slug();
		$isDraft     = $options['isDraft']  ?? $this->isDraft();
		$parent      = $options['parent']   ?? null;
		$parentModel = $options['parent']   ?? $this->site();
		$num         = $options['num']      ?? null;
		$children    = $options['children'] ?? false;
		$files       = $options['files']    ?? false;

		// clean up the slug
		$slug = Url::slug($slug);

		if ($parentModel->findPageOrDraft($slug)) {
			throw new DuplicateException(
				key: 'page.duplicate',
				data: ['slug' => $slug]
			);
		}

		$tmp = new static([
			'isDraft' => $isDraft,
			'num'     => $num,
			'parent'  => $parent,
			'slug'    => $slug,
		]);

		$ignore = [];

		// don't copy files
		if ($files === false) {
			foreach ($this->files() as $file) {
				$ignore[] = $file->root();

				// append all content files
				array_push($ignore, ...$file->storage()->contentFiles(VersionId::latest()));
				array_push($ignore, ...$file->storage()->contentFiles(VersionId::changes()));
			}
		}

		Dir::copy($this->root(), $tmp->root(), $children, $ignore);

		$copy = $parentModel->clone()->findPageOrDraft($slug);

		// normalize copy object
		$copy = PageCopy::process(
			copy: $copy,
			original: $this,
			withFiles: $files,
			withChildren: $children
		);

		// add copy to siblings
		ModelState::update(
			method: 'append',
			current: $copy,
			parent: $parentModel
		);

		return $copy;
	}

	/**
	 * Creates and stores a new page
	 */
	public static function create(array $props): Page
	{
		$props = self::normalizeProps($props);

		// create the instance without content or translations
		// to avoid that the page is created in memory storage
		$page = static::factory([
			...$props,
			'content'      => null,
			'translations' => null
		]);

		// merge the content with the defaults
		$props['content'] = [
			...$page->createDefaultContent(),
			...$props['content'],
		];

		// make sure that a UUID gets generated
		// and added to content right away
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] ??= Uuid::generate();
		}

		// keep the initial storage class
		$storage = $page->storage()::class;

		// make sure that the temporary page is stored in memory
		$page->changeStorage(MemoryStorage::class);

		// inject the content
		$page->setContent($props['content']);

		// inject the translations
		$page->setTranslations($props['translations'] ?? null);

		// run the hooks and creation action
		$page = $page->commit(
			'create',
			[
				'page'  => $page,
				'input' => $props
			],
			function ($page) use ($storage) {
				// move to final storage
				return $page->changeStorage($storage);
			}
		);

		// publish the new page if a number is given
		if (isset($props['num']) === true) {
			$page = $page->changeStatus('listed', $props['num']);
		}

		return $page;
	}

	/**
	 * Creates a child of the current page
	 */
	public function createChild(array $props): Page
	{
		$props = [
			...$props,
			'url'    => null,
			'num'    => null,
			'parent' => $this,
			'site'   => $this->site(),
		];

		$modelClass = static::$models[$props['template'] ?? null] ?? static::class;
		return $modelClass::create($props);
	}

	/**
	 * Create the sorting number for the page
	 * depending on the blueprint settings
	 */
	public function createNum(int|null $num = null): int
	{
		$mode = $this->blueprint()->num();

		switch ($mode) {
			case 'zero':
				return 0;
			case 'date':
			case 'datetime':
				// the $format needs to produce only digits,
				// so it can be converted to integer below
				$format = $mode === 'date' ? 'Ymd' : 'YmdHi';
				$field  = $this->content('default')->get('date');
				$date   = $field->isEmpty() ? 'now' : $field;
				return (int)date($format, strtotime($date));
			case 'default':

				$max = $this
					->parentModel()
					->children()
					->listed()
					->merge($this)
					->count();

				// default positioning at the end
				$num ??= $max;

				// avoid zeros or negative numbers
				if ($num < 1) {
					return 1;
				}

				// avoid higher numbers than possible
				if ($num > $max) {
					return $max;
				}

				return $num;
			default:
				// get instance with default language
				$app = $this->kirby()->clone([], false);
				$app->setCurrentLanguage();

				$template = Str::template($mode, [
					'kirby' => $app,
					'page'  => $this,
					'site'  => $app->site(),
				], ['fallback' => '']);

				return (int)$template;
		}
	}

	/**
	 * Deletes the page
	 */
	public function delete(bool $force = false): bool
	{
		return $this->commit('delete', ['page' => $this, 'force' => $force], function ($page, $force) {
			// clear UUID cache
			$page->uuid()?->clear();

			// delete all files individually
			foreach ($page->files() as $file) {
				$file->delete();
			}

			// delete all children individually
			foreach ($page->children() as $child) {
				$child->delete(true);
			}

			// delete all versions,
			// the plain text storage handler will then clean
			// up the directory if it's empty
			$page->versions()->delete();

			if ($page->isDraft() === false) {
				$page->resortSiblingsAfterUnlisting();
			}

			return true;
		});
	}

	/**
	 * Duplicates the page with the given
	 * slug and optionally copies all files
	 */
	public function duplicate(string|null $slug = null, array $options = []): static
	{
		// create the slug for the duplicate
		$slug = Url::slug($slug ?? $this->slug() . '-' . Url::slug(I18n::translate('page.duplicate.appendix')));

		$arguments = [
			'originalPage' => $this,
			'input'        => $slug,
			'options'      => $options
		];

		return $this->commit('duplicate', $arguments, function ($page, $slug, $options) {
			$page = $this->copy([
				'parent'   => $this->parent(),
				'slug'     => $slug,
				'isDraft'  => true,
				'files'    => $options['files']    ?? false,
				'children' => $options['children'] ?? false,
			]);

			if (isset($options['title']) === true) {
				$page = $page->changeTitle($options['title']);
			}

			return $page;
		});
	}

	/**
	 * Moves the page to a new parent if the
	 * new parent accepts the page type
	 */
	public function move(Site|Page $parent): Page
	{
		// nothing to move
		if ($this->parentModel()->is($parent) === true) {
			return $this;
		}

		$arguments = [
			'page'   => $this,
			'parent' => $parent
		];

		return $this->commit('move', $arguments, function ($page, $parent) {
			// remove the uuid cache for this page
			$page->uuid()?->clear(true);

			// move drafts into the drafts folder of the parent
			$newRoot = match ($page->isDraft()) {
				true  => $parent->root() . '/_drafts/' . $page->dirname(),
				false => $parent->root() . '/' . $page->dirname()
			};

			// try to move the page directory on disk
			if (Dir::move($page->root(), $newRoot) !== true) {
				throw new LogicException(
					key: 'page.move.directory'
				);
			}

			// flush all collection caches to be sure that
			// the new child is included afterwards
			$parent->purge();

			// double-check if the new child can actually be found
			if (!$newPage = $parent->childrenAndDrafts()->find($page->slug())) {
				throw new LogicException(
					key: 'page.move.notFound'
				);
			}

			return $newPage;
		});
	}

	protected static function normalizeProps(array $props): array
	{
		$content  = $props['content']  ?? [];
		$template = $props['template'] ?? 'default';

		return [
			...$props,
			'content'  => $content,
			'isDraft'  => $props['isDraft'] ?? $props['draft'] ?? true,
			'model'    => $props['model']   ?? $template,
			'slug'     => Url::slug($props['slug'] ?? $content['title'] ?? null),
			'template' => $template,
		];
	}

	/**
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the folder cannot be moved
	 * @internal
	 */
	public function publish(): static
	{
		if ($this->isDraft() === false) {
			return $this;
		}

		$page = $this->clone([
			'isDraft'  => false,
			'root'     => null,
			'template' => $this->intendedTemplate()->name(),
		]);

		// actually do it on disk
		if ($this->exists() === true) {
			if (Dir::move($this->root(), $page->root()) !== true) {
				throw new LogicException(
					message: 'The draft folder cannot be moved'
				);
			}

			// Get the draft folder and check if there are any other drafts
			// left. Otherwise delete it.
			$draftDir = dirname($this->root());

			if (Dir::isEmpty($draftDir) === true) {
				Dir::remove($draftDir);
			}
		}

		// remove the page from the parent drafts and add it to children
		$parentModel = $page->parentModel();
		$parentModel->drafts()->remove($page);
		$parentModel->children()->append($page->id(), $page);

		// update the childrenAndDrafts() cache if it is initialized
		if ($parentModel->childrenAndDrafts !== null) {
			$parentModel->childrenAndDrafts()->set($page->id(), $page);
		}

		return $page;
	}

	/**
	 * Clean internal caches
	 *
	 * @return $this
	 */
	public function purge(): static
	{
		parent::purge();

		$this->blueprint         = null;
		$this->children          = null;
		$this->childrenAndDrafts = null;
		$this->drafts            = null;
		$this->files             = null;
		$this->inventory         = null;

		return $this;
	}

	/**
	 * @throws \Kirby\Exception\LogicException If the page is not included in the siblings collection
	 */
	protected function resortSiblingsAfterListing(int|null $position = null): bool
	{
		// get all siblings including the current page
		$siblings = $this
			->parentModel()
			->children()
			->listed()
			->append($this)
			->filter(fn ($page) => $page->blueprint()->num() === 'default');

		// get a non-associative array of ids
		$keys  = $siblings->keys();
		$index = array_search($this->id(), $keys);

		// if the page is not included in the siblings something went wrong
		if ($index === false) {
			throw new LogicException(
				message: 'The page is not included in the sorting index'
			);
		}

		if ($position > count($keys)) {
			$position = count($keys);
		}

		// move the current page number in the array of keys
		// subtract 1 from the num and the position, because of the
		// zero-based array keys
		$sorted = A::move($keys, $index, $position - 1);

		foreach ($sorted as $key => $id) {
			if ($id === $this->id()) {
				continue;
			}

			$siblings->get($id)?->changeNum($key + 1);
		}

		$parent = $this->parentModel();
		$parent->children = $parent->children()->sort('num', 'asc');
		$parent->childrenAndDrafts = null;

		return true;
	}

	/**
	 * @internal
	 */
	public function resortSiblingsAfterUnlisting(): bool
	{
		$index    = 0;
		$parent   = $this->parentModel();
		$siblings = $parent
			->children()
			->listed()
			->not($this)
			->filter(fn ($page) => $page->blueprint()->num() === 'default');

		if ($siblings->count() > 0) {
			foreach ($siblings as $sibling) {
				$index++;
				$sibling->changeNum($index);
			}

			$parent->children = $siblings->sort('num', 'asc');
			$parent->childrenAndDrafts = null;
		}

		return true;
	}

	/**
	 * Convert a page from listed or
	 * unlisted to draft.
	 * @internal
	 *
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the folder cannot be moved
	 */
	public function unpublish(): static
	{
		if ($this->isDraft() === true) {
			return $this;
		}

		$page = $this->clone([
			'isDraft'  => true,
			'num'      => null,
			'dirname'  => null,
			'root'     => null,
			'template' => $this->intendedTemplate()->name(),
		]);

		// actually do it on disk
		if ($this->exists() === true) {
			if (Dir::move($this->root(), $page->root()) !== true) {
				throw new LogicException(
					message: 'The page folder cannot be moved to drafts'
				);
			}
		}

		// remove the page from the parent children and add it to drafts
		$parentModel = $page->parentModel();
		$parentModel->children()->remove($page);
		$parentModel->drafts()->append($page->id(), $page);

		// update the childrenAndDrafts() cache if it is initialized
		if ($parentModel->childrenAndDrafts !== null) {
			$parentModel->childrenAndDrafts()->set($page->id(), $page);
		}

		$page->resortSiblingsAfterUnlisting();

		return $page;
	}

	/**
	 * Updates the page data
	 */
	public function update(
		array|null $input = null,
		string|null $languageCode = null,
		bool $validate = false
	): static {
		if ($this->isDraft() === true) {
			$validate = false;
		}

		$page = parent::update($input, $languageCode, $validate);

		// if num is created from page content, update num on content update
		if (
			$page->isListed() === true &&
			in_array($page->blueprint()->num(), ['zero', 'default'], true) === false
		) {
			$page = $page->changeNum($page->createNum());
		}

		return $page;
	}

	/**
	 * Updates parent collections with the new page object
	 * after a page action
	 *
	 * @deprecated 5.0.0 Use ModelState::update instead
	 */
	protected static function updateParentCollections(
		Page $page,
		string|false $method,
		Page|Site|null $parentModel = null
	): void {
		ModelState::update(
			method: $method,
			current: $page,
			next: $page,
			parent: $parentModel
		);
	}
}
