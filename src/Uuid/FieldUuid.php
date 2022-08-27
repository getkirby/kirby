<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Collection;
use Kirby\Cms\Field;
use Kirby\Toolkit\A;

/**
 * Base for Uuids for models form content fields,
 * such as blocks and structure entries
 *
 * Not yet supported
 * @todo Finish for uuid-block-structure-support
 * @codeCoverageIgnore
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class FieldUuid extends Uuid
{
	protected const FIELD = 'field';

	/**
	 * Converts content field to a related
	 * models collection (e.g. Blocks or Structure)
	 * @internal
	 */
	abstract public static function fieldToCollection(Field $field): Collection;

	/**
	 * Look up Uuid in cache and resolve
	 * to object
	 */
	protected function findByCache(): Identifiable|null
	{
		// get mixed Uri from cache
		$key   = $this->key();
		$value = Uuids::cache()->get($key);

		if ($value === null) {
			return null;
		}

		// value is itself another UUID protocol string
		// e.g. page://page-uuid/myField/the-uuid
		$uri = new Uri($value);

		// resolve e.g. page://page-uuid
		$parent     = Uuid::for($uri->base())->resolve();
		$field      = $uri->path()->first();
		$id		    = $uri->path()->last();
		$field      = $parent->$field();
		$collection = $this->fieldToCollection($field);

		return $collection->get($id);
	}

	/**
	 * Look up Uuid in local and global index
	 * and return the identifiable model object.
	 */
	protected function findByIndex(): Identifiable|null
	{
		foreach ($this->indexes() as $collection) {
			if ($found = $collection->get($this->id())) {
				return $found;
			}
		}

		return null;
	}

	/**
	 * Generator function that returns collections for all fields globally
	 * (in any page's, file's, user's or site's content file)
	 *
	 * @return \Generator|\Kirby\Cms\Collection[]
	 */
	public static function index(): Generator
	{
		$generate = function (Generator $models): Generator {
			foreach ($models as $model) {
				$fields = $model->blueprint()->fields();

				foreach ($fields as $name => $field) {
					if (A::get($field, 'type') === static::FIELD) {
						yield static::fieldToCollection($model->$name());
					}
				}
			}
		};

		yield from $generate(SiteUuid::index());
		yield from $generate(PageUuid::index());
		yield from $generate(FileUuid::index());
		yield from $generate(UserUuid::index());
	}

	/**
	 * Returns value to be stored in cache,
	 * constisting of three parts:
	 * - parent UUID incl. schema
	 * - field name
	 * - UUID id string for model
	 *
	 * e.g. `page://my-page-uuid/myField/my-block-uuid`
	 */
	public function value(): string
	{
		$model  = $this->resolve();
		$parent = Uuid::for($model->parent());

		// populate parent to cache itself as we'll need it
		// as well when resolving model later on
		$parent->populate();

		return $parent->render() . '/' . $model->field()->key() . '/' . $model->id();
	}
}
