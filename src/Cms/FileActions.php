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
	 * @throws \Kirby\Exception\LogicException
	 */
	public function changeName(string $name, bool $sanitize = true): static
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

			// move the content storage versions
			foreach ($oldFile->storage()->all() as $version => $lang) {
				$content = $oldFile->storage()->read($version, $lang);
				$oldFile->storage()->delete($version, $lang);
				$newFile->storage()->create($version, $lang, $content);
			}

			// update collections
			$newFile->parent()->files()->remove($oldFile->id());
			$newFile->parent()->files()->set($newFile->id(), $newFile);

			return $newFile;
		});
	}

	/**
	 * Changes the file's sorting number in the meta file
	 */
	public function changeSort(int $sort): static
	{
		return $this->commit(
			'changeSort',
			['file' => $this, 'position' => $sort],
			fn ($file, $sort) => $file->save(['sort' => $sort])
		);
	}

	/**
	 * @return $this|static
	 */
	public function changeTemplate(string|null $template): static
	{
		if ($template === $this->template()) {
			return $this;
		}

		$arguments = [
			'file'     => $this,
			'template' => $template ?? 'default'
		];

		return $this->commit('changeTemplate', $arguments, function ($oldFile, $template) {
			// convert to new template/blueprint incl. content
			$file = $oldFile->convertTo($template);

			// update template, prefer unset over writing `default`
			if ($template === 'default') {
				$file = $file->update(['template' => null]);
			} else {
				$file = $file->update(['template' => $template]);
			}

			// resize the file if configured by new blueprint
			$file->manipulate($file->blueprint()->create());

			return $file;
		});
	}

	/**
	 * Commits a file action, by following these steps
	 *
	 * 1. checks the action rules
	 * 2. sends the before hook
	 * 3. commits the store action
	 * 4. sends the after hook
	 * 5. returns the result
	 */
	protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed {
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
	 */
	public function copy(Page $page): static
	{
		F::copy($this->root(), $page->root() . '/' . $this->filename());
		$copy = $page->clone()->file($this->filename());

		foreach ($this->storage()->all() as $version => $lang) {
			$content = $this->storage()->read($version, $lang);
			$copy->storage()->create($version, $lang, $content);
		}

		// ensure the content is re-read after copying it
		// @todo find a more elegant way
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
	 * @param bool $move If set to `true`, the source will be deleted
	 * @throws \Kirby\Exception\InvalidArgumentException
	 * @throws \Kirby\Exception\LogicException
	 */
	public static function create(array $props, bool $move = false): static
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

			// store the content if necessary
			// (always create files in the default language)
			$file->save(
				$file->content()->toArray(),
				$file->kirby()->defaultLanguage()?->code()
			);

			// add the file to the list of siblings
			$file->siblings()->append($file->id(), $file);

			// resize the file on upload if configured
			$file->manipulate($file->blueprint()->create());

			// return a fresh clone
			return $file->clone();
		});
	}

	/**
	 * Deletes the file. The store is used to
	 * manipulate the filesystem or whatever you prefer.
	 */
	public function delete(): bool
	{
		return $this->commit('delete', ['file' => $this], function ($file) {
			// remove all public versions, lock and clear UUID cache
			$file->unpublish();

			foreach ($file->storage()->all() as $version => $lang) {
				$file->storage()->delete($version, $lang);
			}

			F::remove($file->root());

			// remove the file from the sibling collection
			$file->parent()->files()->remove($file);

			return true;
		});
	}

	/**
	 * Resizes/crops the original file with Kirby's thumb handler
	 */
	public function manipulate(array|null $options = []): static
	{
		// nothing to process
		if (empty($options) === true || $this->isResizable() === false) {
			return $this;
		}

		$this->kirby()->thumb($this->root(), $this->root(), $options);

		// returns a cloned version of the file
		return $this->clone();
	}

	/**
	 * Move the file to the public media folder
	 * if it's not already there.
	 *
	 * @return $this
	 */
	public function publish(): static
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
	 * @param bool $move If set to `true`, the source will be deleted
	 * @throws \Kirby\Exception\LogicException
	 */
	public function replace(string $source, bool $move = false): static
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

			// apply the resizing/crop options from the blueprint
			$file->manipulate($file->blueprint()->create());

			// return a fresh clone
			return $file->clone();
		});
	}

	/**
	 * Stores the content on disk
	 * @internal
	 */
	public function save(
		array $data = null,
		string $languageCode = null,
		bool $overwrite = false
	): static {
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
	public function unpublish(bool $onlyMedia = false): static
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

	/**
	 * Updates the file's data and ensures that
	 * media files get wiped if `focus` changed
	 *
	 * @return static
	 * @throws \Kirby\Exception\InvalidArgumentException If the input array contains invalid values
	 */
	public function update(
		array $input = null,
		string $languageCode = null,
		bool $validate = false
	) {
		// delete all public media versions when focus field gets changed
		if ($input !== null && array_key_exists('focus', $input) === true) {
			$this->unpublish(true);
		}

		return parent::update($input, $languageCode, $validate);
	}
}
