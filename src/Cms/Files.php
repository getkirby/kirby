<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Uuid\HasUuids;
use Throwable;

/**
 * The `$files` object extends the general
 * `Collection` class and refers to a
 * collection of files, i.e. images, documents
 * etc. Files can be filtered, searched,
 * converted, modified or evaluated with the
 * following methods:
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TFile of \Kirby\Cms\File
 * @extends \Kirby\Cms\Collection<TFile>
 */
class Files extends Collection
{
	use HasUuids;

	/**
	 * All registered files methods
	 */
	public static array $methods = [];

	/**
	 * @var \Kirby\Cms\Page|\Kirby\Cms\Site|\Kirby\Cms\User
	 */
	protected object|null $parent = null;

	/**
	 * Adds a single file or
	 * an entire second collection to the
	 * current collection
	 *
	 * @param \Kirby\Cms\Files<TFile>|TFile|string $object
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException When no `File` or `Files` object or an ID of an existing file is passed
	 */
	public function add($object): static
	{
		// add a files collection
		if ($object instanceof self) {
			$this->data = [...$this->data, ...$object->data];

		// add a file by id
		} elseif (
			is_string($object) === true &&
			$file = App::instance()->file($object)
		) {
			$this->__set($file->id(), $file);

		// add a file object
		} elseif ($object instanceof File) {
			$this->__set($object->id(), $object);

		// give a useful error message on invalid input;
		// silently ignore "empty" values for compatibility with existing setups
		} elseif (in_array($object, [null, false, true], true) !== true) {
			throw new InvalidArgumentException(
				message: 'You must pass a Files or File object or an ID of an existing file to the Files collection'
			);
		}

		return $this;
	}

	/**
	 * Sort all given files by the
	 * order in the array
	 *
	 * @param array $files List of file ids
	 * @param int $offset Sorting offset
	 * @return $this
	 */
	public function changeSort(array $files, int $offset = 0): static
	{
		foreach ($files as $filename) {
			if ($file = $this->get($filename)) {
				$offset++;
				$file->changeSort($offset);
			}
		}

		return $this;
	}

	/**
	 * Deletes the files with the given IDs
	 * if they exist in the collection
	 *
	 * @throws \Kirby\Exception\Exception If not all files could be deleted
	 */
	public function delete(array $ids): void
	{
		$exceptions = [];

		// delete all pages and collect errors
		foreach ($ids as $id) {
			try {
				$model = $this->get($id);

				if ($model instanceof File === false) {
					throw new NotFoundException(
						key: 'file.undefined'
					);
				}

				$model->delete();
			} catch (Throwable $e) {
				$exceptions[$id] = $e;
			}
		}

		if ($exceptions !== []) {
			throw new Exception(
				key: 'file.delete.multiple',
				details: $exceptions
			);
		}
	}

	/**
	 * Creates a files collection from an array of props
	 */
	public static function factory(
		array $files,
		Page|Site|User $parent
	): static {
		$collection = new static([], $parent);

		foreach ($files as $props) {
			$props['collection'] = $collection;
			$props['parent']     = $parent;

			$file = File::factory($props);

			$collection->data[$file->id()] = $file;
		}

		return $collection;
	}

	/**
	 * Finds a file by its filename
	 * @internal Use `$files->find()` instead
	 * @return TFile|null
	 */
	public function findByKey(string $key): File|null
	{
		if ($file = $this->findByUuid($key, 'file')) {
			return $file;
		}

		return $this->get(ltrim($this->parent?->id() . '/' . $key, '/'));
	}

	/**
	 * Returns the file size for all
	 * files in the collection in a
	 * human-readable format
	 * @since 3.6.0
	 *
	 * @param string|null|false $locale Locale for number formatting,
	 *                                  `null` for the current locale,
	 *                                  `false` to disable number formatting
	 */
	public function niceSize(string|false|null $locale = null): string
	{
		return F::niceSize($this->size(), $locale);
	}

	/**
	 * Returns the raw size for all
	 * files in the collection
	 * @since 3.6.0
	 */
	public function size(): int
	{
		return F::size($this->values(fn ($file) => $file->root()));
	}

	/**
	 * Returns the collection sorted by
	 * the sort number and the filename
	 * @return \Kirby\Cms\Files<TFile>
	 */
	public function sorted(): static
	{
		return $this->sort('sort', 'asc', 'filename', 'asc');
	}

	/**
	 * Filter all files by the given template
	 *
	 * @return $this|static
	 */
	public function template(string|array|null $template): static
	{
		if (empty($template) === true) {
			return $this;
		}

		if ($template === 'default') {
			$template = ['default', ''];
		}

		return $this->filter(
			'template',
			is_array($template) ? 'in' : '==',
			$template
		);
	}
}
