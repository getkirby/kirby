<?php

namespace Kirby\Uuid;

use Kirby\Cms\Field;
use Kirby\Cms\Structure;

/**
 * Uuid for \Kirby\Cms\StructureObject
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class StructureUuid extends FieldUuid
{
	protected const TYPE  = 'struct';
	protected const FIELD = 'structure';

	/**
	 * @var \Kirby\Cms\StructureObject|null
	 */
	public Identifiable|null $model;

	/**
	 * Converts content field to a Structure collection
	 * @internal
	 */
	public static function fieldToCollection(Field $field): Structure
	{
		return $field->toStructure();
	}
}
