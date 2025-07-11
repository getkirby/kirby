<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\ImmutableMemoryStorage;
use Kirby\Content\MemoryStorage;
use Kirby\Content\VersionCache;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
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
	protected function changeExtension(
		File $file,
		string|null $extension = null
	): File {
		return $file->changeName($file->name(), false, $extension);
	}

	/**
	 * Renames the file (optionally also the extension).
	 * The store is used to actually execute this.
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function changeName(
		string $name,
		bool $sanitize = true,
		string|null $extension = null
	): static {
		if ($sanitize === true) {
			// sanitize the basename part only
			// as the extension isn't included in $name
			$name = F::safeBasename($name, false);
		}

		// if no extension is passed, make sure to maintain current one
		$extension ??= $this->extension();

		// don't rename if not necessary
		if (
			$name === $this->name() &&
			$extension === $this->extension()
		) {
			return $this;
		}

		return $this->commit('changeName', ['file' => $this, 'name' => $name, 'extension' => $extension], function ($oldFile, $name, $extension) {
			$newFile = $oldFile->clone([
				'filename' => $name . '.' . $extension,
			]);

			// remove all public versions, lock and clear UUID cache
			$oldFile->unpublish();

			if ($oldFile->exists() === false) {
				return $newFile;
			}

			if ($newFile->exists() === true) {
				throw new LogicException(
					message: 'The new file exists and cannot be overwritten'
				);
			}

			// rename the main file
			F::move($oldFile->root(), $newFile->root());

			// hard reset for the version cache
			// to avoid broken/overlapping file references
			VersionCache::reset();

			// move the content storage versions
			$oldFile->storage()->moveAll(to: $newFile->storage());

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
		// skip if the sort number stays the same
		if ($this->sort()->value() === $sort) {
			return $this;
		}

		$arguments = [
			'file'     => $this,
			'position' => $sort
		];

		return $this->commit(
			'changeSort',
			$arguments,
			function ($file, $sort) {
				// make sure to update the sort in the changes version as well
				// otherwise the new sort would be lost as soon as the changes are saved
				if ($file->version('changes')->exists() === true) {
					$file->version('changes')->update(['sort' => $sort]);
				}

				return $file->save(['sort' => $sort]);
			}
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

			// resize the file if configured by new blueprint
			$create = $file->blueprint()->create();
			$file   = $file->manipulate($create);

			return $file;
		});
	}

	/**
	 * Commits a file action, by following these steps
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
	 * Copy the file to the given page
	 */
	public function copy(Page $page): static
	{
		F::copy($this->root(), $page->root() . '/' . $this->filename());

		$copy = new static([
			'parent'   => $page,
			'filename' => $this->filename(),
		]);

		$this->storage()->copyAll(to: $copy->storage());

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
		$props = static::normalizeProps($props);

		// create the basic file and a test upload object
		$file = File::factory([
			...$props,
			'content'      => null,
			'translations' => null,
		]);

		$upload   = $file->assetFactory($props['source']);
		$existing = null;

		// merge the content with the defaults
		$props['content'] = [
			...$file->createDefaultContent(),
			...$props['content'],
		];

		// reuse the existing content if the uploaded file
		// is identical to an existing file
		if ($file->exists() === true) {
			$existing = $file->parent()->file($file->filename());

			if (
				$file->sha1() === $upload->sha1() &&
				$file->template() === $existing->template()
			) {
				// read the content of the existing file and use it
				$props['content'] = $existing->content()->toArray();
			}
		}

		// make sure that a UUID gets generated
		// and added to content right away
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] ??= Uuid::generate();
		}

		// keep the initial storage class
		$storage = $file->storage()::class;

		// make sure that the temporary page is stored in memory
		$file->changeStorage(
			toStorage: MemoryStorage::class,
			// when there’s already an existing file,
			// we need to make sure that the content is
			// copied to memory and the existing content
			// storage entry is not deleted by this step
			copy: $existing !== null
		);

		// inject the content
		$file->setContent($props['content']);

		// inject the translations
		$file->setTranslations($props['translations'] ?? null);

		// if the format is different from the original,
		// we need to already rename it so that the correct file rules
		// are applied
		$create = $file->blueprint()->create();

		// run the hook
		$arguments = compact('file', 'upload');
		return $file->commit('create', $arguments, function ($file, $upload) use ($create, $move, $storage) {
			// remove all public versions, lock and clear UUID cache
			$file->unpublish();

			// only move the original source if intended
			$method = $move === true ? 'move' : 'copy';

			// overwrite the original
			if (F::$method($upload->root(), $file->root(), true) !== true) {
				// @codeCoverageIgnoreStart
				throw new LogicException(
					message: 'The file could not be created'
				);
				// @codeCoverageIgnoreEnd
			}

			// resize the file on upload if configured
			$file = $file->manipulate($create);

			// store the content if necessary
			$file->changeStorage($storage);

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
			$old = $file->clone();

			// keep the content in iummtable memory storage
			// to still have access to it in after hooks
			$file->changeStorage(ImmutableMemoryStorage::class);

			// clear UUID cache
			$file->uuid()?->clear();

			// remove all public versions and clear the UUID cache
			$old->unpublish();

			// delete all versions
			$old->versions()->delete();

			// delete the file from disk
			F::remove($old->root());

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

		// generate image file and overwrite it in place
		$this->kirby()->thumb($this->root(), $this->root(), $options);

		$file = $this->clone();

		// change the file extension if format option configured
		if ($format = $options['format'] ?? null) {
			$file = $file->changeExtension($file, $format);
		}

		return $file;
	}

	protected static function normalizeProps(array $props): array
	{
		if (isset($props['source'], $props['parent']) === false) {
			throw new InvalidArgumentException(
				message: 'Please provide the "source" and "parent" props for the File'
			);
		}

		$content  = $props['content']  ?? [];
		$template = $props['template'] ?? 'default';

		// prefer the filename from the props
		$filename   = $props['filename'] ?? null;
		$filename ??= basename($props['source']);
		$filename   = F::safeName($props['filename']);

		return [
			...$props,
			'content'  => $content,
			'filename' => $filename,
			'model'    => $props['model'] ?? $template,
			'template' => $template,
		];
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
				throw new LogicException(
					message: 'The file could not be created'
				);
			}

			// apply the resizing/crop options from the blueprint
			$create = $file->blueprint()->create();
			$file   = $file->manipulate($create);

			// return a fresh clone
			return $file->clone();
		});
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
			// clear UUID cache
			$this->uuid()?->clear();
		}

		return $this;
	}

	/**
	 * Updates the file's data and ensures that
	 * media files get wiped if `focus` changed
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the input array contains invalid values
	 */
	public function update(
		array|null $input = null,
		string|null $languageCode = null,
		bool $validate = false
	): static {
		// delete all public media versions when focus field gets changed
		if (($input['focus'] ?? null) !== $this->focus()->value()) {
			$this->unpublish(true);
		}

		return parent::update($input, $languageCode, $validate);
	}
}
