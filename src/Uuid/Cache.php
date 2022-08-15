<?php

namespace Kirby\Uuid;

use Kirby\Cache\Cache as BaseCache;
use Kirby\Cms\App;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;

/**
 * Handles the caching of UUIDs.
 * Only stores pages, files, blocks and structure objects in
 * its cache as site and users can be looked up directly via ID
 * (and thus caching wouldn't have any performance benefits)
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Cache
{
	public function __construct(protected Uuid $uuid)
	{
	}

	/**
	 * Removes cache entry
	 */
	public function clear(): bool
	{
		return static::store()->remove($this->key());
	}

	/**
	 * Checks if already cached
	 */
	public function exists(): bool
	{
		return static::store()->exists($this->key());
	}

	/**
	 * Look up UUID in cache and resolve
	 * to identifiable model object
	 */
	public static function find(Uuid $uuid): Identifiable|null
	{
		// get page/file id from cache
		$key   = $uuid->cache()->key();
		$value = static::store()->get($key);

		if ($value === null) {
			return null;
		}

		$type = $uuid->type();

		switch ($type) {
			case 'page':
				$model = App::instance()->page($value);
				break;

			case 'file':
				// value is itself another UUID protocol string
				// e.g. page://page-uuid/filename.jpg
				$uuid = new Uri($value);

				// we need to resolve the parent UUID to its model
				// and then query for the file by filename
				$parent   = Uuid::for($uuid->base())->resolve();
				$filename = $uuid->path()->toString();
				$model    = $parent->file($filename);
				break;

				// @codeCoverageIgnoreStart
			case 'block':
			case 'struct':
				// value is itself another UUID protocol string
				// e.g. page://page-uuid/myField/the-uuid
				$uuid = new Uri($value);

				// resolve e.g. page://page-uuid
				$parent = Uuid::for($uuid->base())->resolve();
				$field  = $uuid->path()->first();
				$id		= $uuid->path()->last();
				$field  = $parent->$field();

				$collection = match ($type) {
					'block'  => $field->toBlocks(),
					'struct' => $field ->toStructure()
				};

				// TODO:ist this really how we cna pick one out the crowd?
				$model = $collection->get($id);
				break;
			// @codeCoverageIgnoreEnd
		}

		return $model;
	}

	/**
	 * Returns key for cache entry
	 */
	public function key(): string
	{
		$id = $this->uuid->render();

		// remove schema
		$id = Str::after($id, '://');

		// for better performance when using a file-based cache,
		// turn first two characters of the id into a directory
		$id = substr($id, 0, 2) . '/' . substr($id, 2);

		return $this->uuid->type() . '/' . $id;
	}

	/**
	 * Write entry to cache
	 *
	 * @return bool
	 */
	public function populate(): bool
	{
		return static::store()->set($this->key(), $this->value());
	}

	/**
	 * Get instance for lookup cache
	 */
	public static function store(): BaseCache
	{
		return App::instance()->cache('uuid');
	}

	/**
	 * Returns value to be stored in cache
	 */
	public function value(): string
	{
		$model = $this->uuid->resolve();

		if ($model === null) {
			throw new LogicException('UUID could not be resolved to model');
		}

		/** @var \Kirby\Cms\Page|\Kirby\Cms\File|\Kirby\Cms\Block|\Kirby\Cms\StructureObject $model */
		return match ($this->uuid->type()) {
			'page' => $model->id(),

			// for files, use parent's UUID as part of the path
			'file' => Uuid::for($model->parent())->render() . '/' . $model->filename(),

			// for blocks and structure objects,
			// use parent's UUID and field name as part of the path
			// TODO: $block->field() doesn't exist yet
			// @codeCoverageIgnoreStart
			'block',
			'struct' => Uuid::for($model->parent())->render() . '/' . $model->field()->name() . '/' . $model->id()
			// @codeCoverageIgnoreEnd
		};
	}
}
