<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\ImmutableMemoryStorage;
use Kirby\Content\MemoryStorage;
use Kirby\Content\VersionCache;
use Kirby\Content\VersionId;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
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
		$slug     = Url::slug($slug);
		$language = Language::ensure($languageCode ?? 'current');

		// in multi-language installations the slug for the non-default
		// languages is stored in the text file. The changeSlugForLanguage
		// method takes care of that.
		if ($language->isDefault() === false) {
			return $this->changeSlugForLanguage($slug, $language->code());
		}

		// if the slug stays exactly the same,
		// nothing needs to be done.
		if ($slug === $this->slug()) {
			return $this;
		}

		$arguments = [
			'page'         => $this,
			'slug'         => $slug,
			'languageCode' => null,
			'language'     => $language
		];

		return $this->commit('changeSlug', $arguments, function ($oldPage, $slug, $languageCode, $language) {
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
				VersionCache::reset();

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
		$language = Language::ensure($languageCode ?? 'current');

		if ($language->isDefault() === true) {
			throw new InvalidArgumentException(
				message: 'Use the changeSlug method to change the slug for the default language'
			);
		}

		$arguments = [
			'page'         => $this,
			'slug'         => $slug,
			'languageCode' => $language->code(),
			'language'     => $language
		];

		return $this->commit('changeSlug', $arguments, function ($page, $slug, $languageCode, $language) {
			// remove the slug if it's the same as the folder name
			if ($slug === $page->uid()) {
				$slug = null;
			}

			// make sure to update the slug in the changes version as well
			// otherwise the new slug would be lost as soon as the changes are saved
			if ($page->version('changes')->exists($language) === true) {
				$page->version('changes')->update(['slug' => $slug], $language);
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
		$language = Language::ensure($languageCode ?? 'current');

		$arguments = [
			'page'         => $this,
			'title'        => $title,
			'languageCode' => $languageCode,
			'language'     => $language
		];

		return $this->commit('changeTitle', $arguments, function ($page, $title, $languageCode, $language) {

			// make sure to update the title in the changes version as well
			// otherwise the new title would be lost as soon as the changes are saved
			if ($page->version('changes')->exists($language) === true) {
				$page->version('changes')->update(['title' => $title], $language);
			}

			return $page->save(['title' => $title], $language->code());
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
		$page = Page::factory([
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

		if (
			($template = $props['template'] ?? null) &&
			($model = static::$models[$template] ?? null)
		) {
			return $model::create($props);
		}

		return static::create($props);
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
			$old = $page->clone();

			// keep the content in iummtable memory storage
			// to still have access to it in after hooks
			$page->changeStorage(ImmutableMemoryStorage::class);

			// clear UUID cache
			$page->uuid()?->clear();

			// Explanation: The two while loops below are only
			// necessary because our property caches result in
			// outdated collections when deleting nested pages.
			// When we use a foreach loop to go through those collections,
			// we encounter outdated objects. Using a while loop
			// fixes this issue.
			//
			// TODO: We can remove this part as soon
			// as we move away from our immutable object architecture.

			// delete all files individually
			while ($file = $page->files()->first()) {
				$file->delete();
			}

			// delete all children individually
			while ($child = $page->childrenAndDrafts()->first()) {
				$child->delete(true);
			}

			// delete all versions,
			// the plain text storage handler will then clean
			// up the directory if it's empty
			$old->versions()->delete();

			if (
				$page->isListed() === true &&
				$page->blueprint()->num() === 'default'
			) {
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
		$parent   = $this->parentModel();
		$siblings = $parent->children();

		// Get all listed siblings including the current page
		$listed = $siblings
			->listed()
			->append($this)
			->filter(fn ($page) => $page->blueprint()->num() === 'default');

		// Get a non-associative array of ids
		$keys  = $listed->keys();
		$index = array_search($this->id(), $keys);

		// If the page is not included in the siblings something went wrong
		if ($index === false) {
			throw new LogicException(
				message: 'The page is not included in the sorting index'
			);
		}

		if ($position > count($keys)) {
			$position = count($keys);
		}

		// Move the current page number in the array of keys.
		// Subtract 1 from the num and the position, because of the
		// zero-based array keys
		$sorted = A::move($keys, $index, $position - 1);

		foreach ($sorted as $key => $id) {
			if ($id === $this->id()) {
				continue;
			}

			// Apply the new sorting number
			// and update the new object in the siblings collection
			$newSibling = $listed->get($id)?->changeNum($key + 1);
			$siblings->update($newSibling);
		}

		// Update the parent's children collection with the new sorting
		$parent->children = $siblings->sort('isListed', 'desc', 'num', 'asc');
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
		$siblings = $parent->children();

		// Get all listed siblings excluding the current page
		$listed = $siblings
			->listed()
			->not($this)
			->filter(fn ($page) => $page->blueprint()->num() === 'default');

		if ($listed->count() > 0) {
			foreach ($listed as $sibling) {
				$index++;

				// Apply the new sorting number
				// and update the new object in the siblings collection
				$newSibling = $sibling->changeNum($index);
				$siblings->update($newSibling);
			}

			// Update the parent's children collection with the new sorting
			$parent->children = $siblings->sort('isListed', 'desc', 'num', 'asc');
			$parent->childrenAndDrafts = null;
		}

		return true;
	}

	/**
	 * Convert a page from listed or unlisted to draft
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

		// remove the media directory
		Dir::remove($this->mediaRoot());

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
