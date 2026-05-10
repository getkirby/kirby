<?php

namespace Kirby\Uuid;

use Kirby\Cms\Structure;
use Kirby\Content\Field;

/**
 * UUID for \Kirby\Cms\StructureObject
 *
 * Not yet supported
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @todo Finish for uuid-block-structure-support
 * @codeCoverageIgnore
 */
class StructureUuid extends FieldUuid
{
	protected const string TYPE  = 'struct';
	protected const string FIELD = 'structure';

	/**
	 * @var \Kirby\Cms\StructureObject|null
	 */
	public Identifiable|null $model = null;

	/**
	 * Converts content field to a Structure collection
	 * @unstable
	 */
	public static function fieldToCollection(Field $field): Structure
	{
		return $field->toStructure();
	}
}
