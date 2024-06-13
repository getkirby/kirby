<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Collection;
use Kirby\Content\Field;
use Kirby\Toolkit\A;

/**
 * Base for UUIDs for models from content fields,
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
	 * Converts a content field to a related
	 * models collection (e.g. Blocks or Structure)
	 * @internal
	 */
	abstract public static function fieldToCollection(Field $field): Collection;

	/**
	 * Looks up UUID in cache and resolves
	 * to identifiable model object
	 */
	protected function findByCache(): Identifiable|null
	{
		// get mixed Uri from cache
		if ($key = $this->key()) {
			if ($value = Uuids::cache()->get($key)) {
				// value is an array containing
				// the UUID for the parent, the field name
				// and the specific ID
				$parent = Uuid::for($value['parent'])->model();

				if ($field = $parent?->content()->get($value['field'])) {
					return static::fieldToCollection($field)->get($value['id']);
				}
			}
		}

		return null;
	}

	/**
	 * Looks up UUID in local and global index
	 * and returns the identifiable model object
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

	/*
	 * Returns the ID for the specific entry/row of the field
	 * (we can rely in this case that the Uri was filled  on initiation)
	 * @todo needs to be ensured for structure field once refactoring
	 */
	public function id(): string
	{
		return $this->uri->host();
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
	 * - parent UUID including scheme
	 * - field name
	 * - UUID id string for model
	 */
	public function value(): array
	{
		$model  = $this->model();
		$parent = Uuid::for($model->parent());

		// populate parent to cache itself as we'll need it
		// as well when resolving model later on
		$parent->populate();

		return [
			'parent' => $parent->toString(),
			'field'  => $model->field()->key(),
			'id'     => $model->id()
		];
	}
}
