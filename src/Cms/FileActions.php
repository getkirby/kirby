<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Kirby\Form\Form;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

/**
 * FileActions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait FileActions
{
	/**
	 * Renames the file without touching the extension
	 * The store is used to actually execute this.
	 *
	 * @param string $name
	 * @param bool $sanitize
	 * @return $this|static
	 * @throws \Kirby\Exception\LogicException
	 */
	public function changeName(string $name, bool $sanitize = true)
	{
		if ($sanitize === true) {
			$name = F::safeName($name);
		}

		// don't rename if not necessary
		if ($name === $this->name()) {
			return $this;
		}

		return $this->commit('changeName', ['file' => $this, 'name' => $name], function ($oldFile, $name) {
			$newFile = $oldFile->clone([
				'filename' => $name . '.' . $oldFile->extension(),
			]);

			// remove all public versions, lock and clear UUID cache
			$oldFile->unpublish();

			if ($oldFile->exists() === false) {
				return $newFile;
			}

			if ($newFile->exists() === true) {
				throw new LogicException('The new file exists and cannot be overwritten');
			}

			// rename the main file
			F::move($oldFile->root(), $newFile->root());

			if ($newFile->kirby()->multilang() === true) {
				foreach ($newFile->translations() as $translation) {
					$translationCode = $translation->code();

					// rename the content file
					F::move($oldFile->contentFile($translationCode), $newFile->contentFile($translationCode));
				}
			} else {
				// rename the content file
				F::move($oldFile->contentFile(), $newFile->contentFile());
			}

			// update collections
			$newFile->parent()->files()->remove($oldFile->id());
			$newFile->parent()->files()->set($newFile->id(), $newFile);

			return $newFile;
		});
	}

	/**
	 * Changes the file's sorting number in the meta file
	 *
	 * @param int $sort
	 * @return static
	 */
	public function changeSort(int $sort)
	{
		return $this->commit(
			'changeSort',
			['file' => $this, 'position' => $sort],
			fn ($file, $sort) => $file->save(['sort' => $sort])
		);
	}

	/**
	 * Commits a file action, by following these steps
	 *
	 * 1. checks the action rules
	 * 2. sends the before hook
	 * 3. commits the store action
	 * 4. sends the after hook
	 * 5. returns the result
	 *
	 * @param string $action
	 * @param array $arguments
	 * @param Closure $callback
	 * @return mixed
	 */
	protected function commit(string $action, array $arguments, Closure $callback)
	{
		$old            = $this->hardcopy();
		$kirby          = $this->kirby();
		$argumentValues = array_values($arguments);

		$this->rules()->$action(...$argumentValues);
		$kirby->trigger('file.' . $action . ':before', $arguments);

		$result = $callback(...$argumentValues);

		$argumentsAfter = match ($action) {
			'create' => ['file' => $result],
			'delete' => ['status' => $result, 'file' => $old],
			default  => ['newFile' => $result, 'oldFile' => $old]
		};

		$kirby->trigger('file.' . $action . ':after', $argumentsAfter);

		$kirby->cache('pages')->flush();
		return $result;
	}

	/**
	 * Copy the file to the given page
	 *
	 * @param \Kirby\Cms\Page $page
	 * @return \Kirby\Cms\File
	 */
	public function copy(Page $page)
	{
		F::copy($this->root(), $page->root() . '/' . $this->filename());

		if ($this->kirby()->multilang() === true) {
			foreach ($this->kirby()->languages() as $language) {
				$contentFile = $this->contentFile($language->code());
				F::copy($contentFile, $page->root() . '/' . basename($contentFile));
			}
		} else {
			$contentFile = $this->contentFile();
			F::copy($contentFile, $page->root() . '/' . basename($contentFile));
		}

		$copy = $page->clone()->file($this->filename());

		// overwrite with new UUID (remove old, add new)
		if (Uuids::enabled() === true) {
			$copy = $copy->save(['uuid' => Uuid::generate()]);
		}

		return $copy;
	}

	/**
	 * Creates a new file on disk and returns the
	 * File object. The store is used to handle file
	 * writing, so it can be replaced by any other
	 * way of generating files.
	 *
	 * @param array $props
	 * @param bool $move If set to `true`, the source will be deleted
	 * @return static
	 * @throws \Kirby\Exception\InvalidArgumentException
	 * @throws \Kirby\Exception\LogicException
	 */
	public static function create(array $props, bool $move = false)
	{
		if (isset($props['source'], $props['parent']) === false) {
			throw new InvalidArgumentException('Please provide the "source" and "parent" props for the File');
		}

		// prefer the filename from the props
		$props['filename'] = F::safeName($props['filename'] ?? basename($props['source']));

		$props['model'] = strtolower($props['template'] ?? 'default');

		// create the basic file and a test upload object
		$file = static::factory($props);
		$upload = $file->asset($props['source']);

		// gather content
		$content = $props['content'] ?? [];

		// make sure that a UUID gets generated and
		// added to content right away
		if (Uuids::enabled() === true) {
			$content['uuid'] ??= Uuid::generate();
		}

		// create a form for the file
		$form = Form::for($file, ['values' => $content]);

		// inject the content
		$file = $file->clone(['content' => $form->strings(true)]);

		// run the hook
		$arguments = compact('file', 'upload');
		return $file->commit('create', $arguments, function ($file, $upload) use ($move) {
			// remove all public versions, lock and clear UUID cache
			$file->unpublish();

			// only move the original source if intended
			$method = $move === true ? 'move' : 'copy';

			// overwrite the original
			if (F::$method($upload->root(), $file->root(), true) !== true) {
				throw new LogicException('The file could not be created');
			}

			// always create pages in the default language
			if ($file->kirby()->multilang() === true) {
				$languageCode = $file->kirby()->defaultLanguage()->code();
			} else {
				$languageCode = null;
			}

			// store the content if necessary
			$file->save($file->content()->toArray(), $languageCode);

			// add the file to the list of siblings
			$file->siblings()->append($file->id(), $file);

			// return a fresh clone
			return $file->clone();
		});
	}

	/**
	 * Deletes the file. The store is used to
	 * manipulate the filesystem or whatever you prefer.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		return $this->commit('delete', ['file' => $this], function ($file) {
			// remove all public versions, lock and clear UUID cache
			$file->unpublish();

			if ($file->kirby()->multilang() === true) {
				foreach ($file->translations() as $translation) {
					F::remove($file->contentFile($translation->code()));
				}
			} else {
				F::remove($file->contentFile());
			}

			F::remove($file->root());

			// remove the file from the sibling collection
			$file->parent()->files()->remove($file);

			return true;
		});
	}

	/**
	 * Move the file to the public media folder
	 * if it's not already there.
	 *
	 * @return $this
	 */
	public function publish()
	{
		Media::publish($this, $this->mediaRoot());
		return $this;
	}

	/**
	 * Replaces the file. The source must
	 * be an absolute path to a file or a Url.
	 * The store handles the replacement so it
	 * finally decides what it will support as
	 * source.
	 *
	 * @param string $source
	 * @param bool $move If set to `true`, the source will be deleted
	 * @return static
	 * @throws \Kirby\Exception\LogicException
	 */
	public function replace(string $source, bool $move = false)
	{
		$file = $this->clone();

		$arguments = [
			'file' => $file,
			'upload' => $file->asset($source)
		];

		return $this->commit('replace', $arguments, function ($file, $upload) use ($move) {
			// delete all public versions
			$file->unpublish(true);

			// only move the original source if intended
			$method = $move === true ? 'move' : 'copy';

			// overwrite the original
			if (F::$method($upload->root(), $file->root(), true) !== true) {
				throw new LogicException('The file could not be created');
			}

			// return a fresh clone
			return $file->clone();
		});
	}

	/**
	 * Stores the content on disk
	 *
	 * @internal
	 * @param array|null $data
	 * @param string|null $languageCode
	 * @param bool $overwrite
	 * @return static
	 */
	public function save(array $data = null, string $languageCode = null, bool $overwrite = false)
	{
		$file = parent::save($data, $languageCode, $overwrite);

		// update model in siblings collection
		$file->parent()->files()->set($file->id(), $file);

		return $file;
	}

	/**
	 * Remove all public versions of this file
	 *
	 * @return $this
	 */
	public function unpublish(bool $onlyMedia = false)
	{
		// unpublish media files
		Media::unpublish($this->parent()->mediaRoot(), $this);

		if ($onlyMedia !== true) {
			// remove the lock
			$this->lock()?->remove();

			// clear UUID cache
			$this->uuid()?->clear();
		}

		return $this;
	}
}
