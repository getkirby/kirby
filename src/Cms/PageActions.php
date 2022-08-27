<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Form\Form;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

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
	 * @param int|null $num
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If a draft is being sorted or the directory cannot be moved
	 */
	public function changeNum(int $num = null)
	{
		if ($this->isDraft() === true) {
			throw new LogicException('Drafts cannot change their sorting number');
		}

		// don't run the action if everything stayed the same
		if ($this->num() === $num) {
			return $this;
		}

		return $this->commit('changeNum', ['page' => $this, 'num' => $num], function ($oldPage, $num) {
			$newPage = $oldPage->clone([
				'num'     => $num,
				'dirname' => null,
				'root'    => null
			]);

			// actually move the page on disk
			if ($oldPage->exists() === true) {
				if (Dir::move($oldPage->root(), $newPage->root()) === true) {
					// Updates the root path of the old page with the root path
					// of the moved new page to use fly actions on old page in loop
					$oldPage->setRoot($newPage->root());
				} else {
					throw new LogicException('The page directory cannot be moved');
				}
			}

			// overwrite the child in the parent page
			static::updateParentCollections($newPage, 'set');

			return $newPage;
		});
	}

	/**
	 * Changes the slug/uid of the page
	 *
	 * @param string $slug
	 * @param string|null $languageCode
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the directory cannot be moved
	 */
	public function changeSlug(string $slug, string $languageCode = null)
	{
		// always sanitize the slug
		$slug = Str::slug($slug);

		// in multi-language installations the slug for the non-default
		// languages is stored in the text file. The changeSlugForLanguage
		// method takes care of that.
		if ($language = $this->kirby()->language($languageCode)) {
			if ($language->isDefault() === false) {
				return $this->changeSlugForLanguage($slug, $languageCode);
			}
		}

		// if the slug stays exactly the same,
		// nothing needs to be done.
		if ($slug === $this->slug()) {
			return $this;
		}

		$arguments = ['page' => $this, 'slug' => $slug, 'languageCode' => null];
		return $this->commit('changeSlug', $arguments, function ($oldPage, $slug) {
			$newPage = $oldPage->clone([
				'slug'    => $slug,
				'dirname' => null,
				'root'    => null
			]);

			if ($oldPage->exists() === true) {
				// remove the lock of the old page
				if ($lock = $oldPage->lock()) {
					$lock->remove();
				}

				// actually move stuff on disk
				if (Dir::move($oldPage->root(), $newPage->root()) !== true) {
					throw new LogicException('The page directory cannot be moved');
				}

				// remove from the siblings
				static::updateParentCollections($oldPage, 'remove');

				Dir::remove($oldPage->mediaRoot());
			}

			// overwrite the new page in the parent collection
			static::updateParentCollections($newPage, 'set');

			return $newPage;
		});
	}

	/**
	 * Change the slug for a specific language
	 *
	 * @param string $slug
	 * @param string|null $languageCode
	 * @return static
	 * @throws \Kirby\Exception\NotFoundException If the language for the given language code cannot be found
	 * @throws \Kirby\Exception\InvalidArgumentException If the slug for the default language is being changed
	 */
	protected function changeSlugForLanguage(string $slug, string $languageCode = null)
	{
		$language = $this->kirby()->language($languageCode);

		if (!$language) {
			throw new NotFoundException('The language: "' . $languageCode . '" does not exist');
		}

		if ($language->isDefault() === true) {
			throw new InvalidArgumentException('Use the changeSlug method to change the slug for the default language');
		}

		$arguments = ['page' => $this, 'slug' => $slug, 'languageCode' => $languageCode];
		return $this->commit('changeSlug', $arguments, function ($page, $slug, $languageCode) {
			// remove the slug if it's the same as the folder name
			if ($slug === $page->uid()) {
				$slug = null;
			}

			$newPage = $page->save(['slug' => $slug], $languageCode);

			// overwrite the updated page in the parent collection
			static::updateParentCollections($newPage, 'set');

			return $newPage;
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
	 * @return static
	 * @throws \Kirby\Exception\InvalidArgumentException If an invalid status is being passed
	 */
	public function changeStatus(string $status, int $position = null)
	{
		switch ($status) {
			case 'draft':
				return $this->changeStatusToDraft();
			case 'listed':
				return $this->changeStatusToListed($position);
			case 'unlisted':
				return $this->changeStatusToUnlisted();
			default:
				throw new InvalidArgumentException('Invalid status: ' . $status);
		}
	}

	/**
	 * @return static
	 */
	protected function changeStatusToDraft()
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
	 * @param int|null $position
	 * @return $this|static
	 */
	protected function changeStatusToListed(int $position = null)
	{
		// create a sorting number for the page
		$num = $this->createNum($position);

		// don't sort if not necessary
		if ($this->status() === 'listed' && $num === $this->num()) {
			return $this;
		}

		$arguments = ['page' => $this, 'status' => 'listed', 'position' => $num];
		$page = $this->commit('changeStatus', $arguments, function ($page, $status, $position) {
			return $page->publish()->changeNum($position);
		});

		if ($this->blueprint()->num() === 'default') {
			$page->resortSiblingsAfterListing($num);
		}

		return $page;
	}

	/**
	 * @return $this|static
	 */
	protected function changeStatusToUnlisted()
	{
		if ($this->status() === 'unlisted') {
			return $this;
		}

		$arguments = ['page' => $this, 'status' => 'unlisted', 'position' => null];
		$page = $this->commit('changeStatus', $arguments, function ($page) {
			return $page->publish()->changeNum(null);
		});

		$this->resortSiblingsAfterUnlisting();

		return $page;
	}

	/**
	 * Change the position of the page in its siblings
	 * collection. Siblings will be resorted. If the page
	 * status isn't yet `listed`, it will be changed to it.
	 *
	 * @param int|null $position
	 * @return $this|static
	 */
	public function changeSort(int $position = null)
	{
		return $this->changeStatus('listed', $position);
	}

	/**
	 * Changes the page template
	 *
	 * @param string $template
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the textfile cannot be renamed/moved
	 */
	public function changeTemplate(string $template)
	{
		if ($template === $this->intendedTemplate()->name()) {
			return $this;
		}

		return $this->commit('changeTemplate', ['page' => $this, 'template' => $template], function ($oldPage, $template) {
			if ($this->kirby()->multilang() === true) {
				$newPage = $this->clone([
					'template' => $template
				]);

				foreach ($this->kirby()->languages()->codes() as $code) {
					if ($oldPage->translation($code)->exists() !== true) {
						continue;
					}

					$content = $oldPage->content($code)->convertTo($template);

					if (F::remove($oldPage->contentFile($code)) !== true) {
						throw new LogicException('The old text file could not be removed');
					}

					// save the language file
					$newPage->save($content, $code);
				}

				// return a fresh copy of the object
				$page = $newPage->clone();
			} else {
				$newPage = $this->clone([
					'content'  => $this->content()->convertTo($template),
					'template' => $template
				]);

				if (F::remove($oldPage->contentFile()) !== true) {
					throw new LogicException('The old text file could not be removed');
				}

				$page = $newPage->save();
			}

			// update the parent collection
			static::updateParentCollections($page, 'set');

			return $page;
		});
	}

	/**
	 * Change the page title
	 *
	 * @param string $title
	 * @param string|null $languageCode
	 * @return static
	 */
	public function changeTitle(string $title, string $languageCode = null)
	{
		$arguments = ['page' => $this, 'title' => $title, 'languageCode' => $languageCode];
		return $this->commit('changeTitle', $arguments, function ($page, $title, $languageCode) {
			$page = $page->save(['title' => $title], $languageCode);

			// flush the parent cache to get children and drafts right
			static::updateParentCollections($page, 'set');

			return $page;
		});
	}

	/**
	 * Commits a page action, by following these steps
	 *
	 * 1. checks the action rules
	 * 2. sends the before hook
	 * 3. commits the store action
	 * 4. sends the after hook
	 * 5. returns the result
	 *
	 * @param string $action
	 * @param array $arguments
	 * @param \Closure $callback
	 * @return mixed
	 */
	protected function commit(string $action, array $arguments, Closure $callback)
	{
		$old            = $this->hardcopy();
		$kirby          = $this->kirby();
		$argumentValues = array_values($arguments);

		$this->rules()->$action(...$argumentValues);
		$kirby->trigger('page.' . $action . ':before', $arguments);

		$result = $callback(...$argumentValues);

		if ($action === 'create') {
			$argumentsAfter = ['page' => $result];
		} elseif ($action === 'duplicate') {
			$argumentsAfter = ['duplicatePage' => $result, 'originalPage' => $old];
		} elseif ($action === 'delete') {
			$argumentsAfter = ['status' => $result, 'page' => $old];
		} else {
			$argumentsAfter = ['newPage' => $result, 'oldPage' => $old];
		}
		$kirby->trigger('page.' . $action . ':after', $argumentsAfter);

		$kirby->cache('pages')->flush();
		return $result;
	}

	/**
	 * Copies the page to a new parent
	 *
	 * @param array $options
	 * @return \Kirby\Cms\Page
	 * @throws \Kirby\Exception\DuplicateException If the page already exists
	 */
	public function copy(array $options = [])
	{
		$slug        = $options['slug']      ?? $this->slug();
		$isDraft     = $options['isDraft']   ?? $this->isDraft();
		$parent      = $options['parent']    ?? null;
		$parentModel = $options['parent']    ?? $this->site();
		$num         = $options['num']       ?? null;
		$children    = $options['children']  ?? false;
		$files       = $options['files']     ?? false;

		// clean up the slug
		$slug = Str::slug($slug);

		if ($parentModel->findPageOrDraft($slug)) {
			throw new DuplicateException([
				'key'  => 'page.duplicate',
				'data' => [
					'slug' => $slug
				]
			]);
		}

		$tmp = new static([
			'isDraft' => $isDraft,
			'num'     => $num,
			'parent'  => $parent,
			'slug'    => $slug,
		]);

		$ignore = [
			$this->kirby()->locks()->file($this)
		];

		// don't copy files
		if ($files === false) {
			foreach ($this->files() as $file) {
				$ignore[] = $file->root();

				// append all content files
				array_push($ignore, ...$file->contentFiles());
			}
		}

		Dir::copy($this->root(), $tmp->root(), $children, $ignore);

		$copy = $parentModel->clone()->findPageOrDraft($slug);

		// remove all translated slugs
		if ($this->kirby()->multilang() === true) {
			foreach ($this->kirby()->languages() as $language) {
				if ($language->isDefault() === false && $copy->translation($language)->exists() === true) {
					$copy = $copy->save(['slug' => null], $language->code());
				}
			}
		}

		// add copy to siblings
		static::updateParentCollections($copy, 'append', $parentModel);

		return $copy;
	}

	/**
	 * Creates and stores a new page
	 *
	 * @param array $props
	 * @return static
	 */
	public static function create(array $props)
	{
		// clean up the slug
		$props['slug']     = Str::slug($props['slug'] ?? $props['content']['title'] ?? null);
		$props['template'] = $props['model'] = strtolower($props['template'] ?? 'default');
		$props['isDraft']  = ($props['draft'] ?? true);

		// create a temporary page object
		$page = Page::factory($props);

		// create a form for the page
		$form = Form::for($page, [
			'values' => $props['content'] ?? []
		]);

		// inject the content
		$page = $page->clone(['content' => $form->strings(true)]);

		// run the hooks and creation action
		$page = $page->commit('create', ['page' => $page, 'input' => $props], function ($page, $props) {
			// always create pages in the default language
			if ($page->kirby()->multilang() === true) {
				$languageCode = $page->kirby()->defaultLanguage()->code();
			} else {
				$languageCode = null;
			}

			// write the content file
			$page = $page->save($page->content()->toArray(), $languageCode);

			// flush the parent cache to get children and drafts right
			static::updateParentCollections($page, 'append');

			return $page;
		});

		// publish the new page if a number is given
		if (isset($props['num']) === true) {
			$page = $page->changeStatus('listed', $props['num']);
		}

		return $page;
	}

	/**
	 * Creates a child of the current page
	 *
	 * @param array $props
	 * @return static
	 */
	public function createChild(array $props)
	{
		$props = array_merge($props, [
			'url'    => null,
			'num'    => null,
			'parent' => $this,
			'site'   => $this->site(),
		]);

		$modelClass = Page::$models[$props['template']] ?? Page::class;
		return $modelClass::create($props);
	}

	/**
	 * Create the sorting number for the page
	 * depending on the blueprint settings
	 *
	 * @param int|null $num
	 * @return int
	 */
	public function createNum(int $num = null): int
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
				$lang   = $this->kirby()->defaultLanguage() ?? null;
				$field  = $this->content($lang)->get('date');
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
				if ($num === null) {
					$num = $max;
				}

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
					'page'  => $app->page($this->id()),
					'site'  => $app->site(),
				], ['fallback' => '']);

				return (int)$template;
		}
	}

	/**
	 * Deletes the page
	 *
	 * @param bool $force
	 * @return bool
	 */
	public function delete(bool $force = false): bool
	{
		return $this->commit('delete', ['page' => $this, 'force' => $force], function ($page, $force) {
			// delete all files individually
			foreach ($page->files() as $file) {
				$file->delete();
			}

			// delete all children individually
			foreach ($page->children() as $child) {
				$child->delete(true);
			}

			// actually remove the page from disc
			if ($page->exists() === true) {
				// delete all public media files
				Dir::remove($page->mediaRoot());

				// delete the content folder for this page
				Dir::remove($page->root());

				// if the page is a draft and the _drafts folder
				// is now empty. clean it up.
				if ($page->isDraft() === true) {
					$draftsDir = dirname($page->root());

					if (Dir::isEmpty($draftsDir) === true) {
						Dir::remove($draftsDir);
					}
				}
			}

			static::updateParentCollections($page, 'remove');

			if ($page->isDraft() === false) {
				$page->resortSiblingsAfterUnlisting();
			}

			return true;
		});
	}

	/**
	 * Duplicates the page with the given
	 * slug and optionally copies all files
	 *
	 * @param string|null $slug
	 * @param array $options
	 * @return \Kirby\Cms\Page
	 */
	public function duplicate(string $slug = null, array $options = [])
	{
		// create the slug for the duplicate
		$slug = Str::slug($slug ?? $this->slug() . '-' . Str::slug(I18n::translate('page.duplicate.appendix')));

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
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the folder cannot be moved
	 */
	public function publish()
	{
		if ($this->isDraft() === false) {
			return $this;
		}

		$page = $this->clone([
			'isDraft' => false,
			'root'    => null
		]);

		// actually do it on disk
		if ($this->exists() === true) {
			if (Dir::move($this->root(), $page->root()) !== true) {
				throw new LogicException('The draft folder cannot be moved');
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
	 * @return $this
	 */
	public function purge()
	{
		$this->blueprint         = null;
		$this->children          = null;
		$this->childrenAndDrafts = null;
		$this->content           = null;
		$this->drafts            = null;
		$this->files             = null;
		$this->inventory         = null;
		$this->translations      = null;

		return $this;
	}

	/**
	 * @param int|null $position
	 * @return bool
	 * @throws \Kirby\Exception\LogicException If the page is not included in the siblings collection
	 */
	protected function resortSiblingsAfterListing(int $position = null): bool
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
			throw new LogicException('The page is not included in the sorting index');
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
			} elseif ($sibling = $siblings->get($id)) {
				$sibling->changeNum($key + 1);
			}
		}

		$parent = $this->parentModel();
		$parent->children = $parent->children()->sort('num', 'asc');
		$parent->childrenAndDrafts = null;

		return true;
	}

	/**
	 * @return bool
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
	 *
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException If the folder cannot be moved
	 */
	public function unpublish()
	{
		if ($this->isDraft() === true) {
			return $this;
		}

		$page = $this->clone([
			'isDraft' => true,
			'num'     => null,
			'dirname' => null,
			'root'    => null
		]);

		// actually do it on disk
		if ($this->exists() === true) {
			if (Dir::move($this->root(), $page->root()) !== true) {
				throw new LogicException('The page folder cannot be moved to drafts');
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
	 *
	 * @param array|null $input
	 * @param string|null $languageCode
	 * @param bool $validate
	 * @return static
	 */
	public function update(array $input = null, string $languageCode = null, bool $validate = false)
	{
		if ($this->isDraft() === true) {
			$validate = false;
		}

		$page = parent::update($input, $languageCode, $validate);

		// if num is created from page content, update num on content update
		if ($page->isListed() === true && in_array($page->blueprint()->num(), ['zero', 'default']) === false) {
			$page = $page->changeNum($page->createNum());
		}

		// overwrite the updated page in the parent collection
		static::updateParentCollections($page, 'set');

		return $page;
	}

	/**
	 * Updates parent collections with the new page object
	 * after a page action
	 *
	 * @param \Kirby\Cms\Page $page
	 * @param string $method Method to call on the parent collections
	 * @param \Kirby\Cms\Page|null $parentMdel
	 * @return void
	 */
	protected static function updateParentCollections($page, string $method, $parentModel = null): void
	{
		$parentModel ??= $page->parentModel();

		// method arguments depending on the called method
		$args = $method === 'remove' ? [$page] : [$page->id(), $page];

		if ($page->isDraft() === true) {
			$parentModel->drafts()->$method(...$args);
		} else {
			$parentModel->children()->$method(...$args);
		}

		// update the childrenAndDrafts() cache if it is initialized
		if ($parentModel->childrenAndDrafts !== null) {
			$parentModel->childrenAndDrafts()->$method(...$args);
		}
	}
}
