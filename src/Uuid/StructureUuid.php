<?php

namespace Kirby\Uuid;

use Kirby\Cms\Structure;
use Kirby\Content\Field;

/**
 * UUID for \Kirby\Cms\StructureObject
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
class StructureUuid extends FieldUuid
{
	protected const TYPE  = 'struct';
	protected const FIELD = 'structure';

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
